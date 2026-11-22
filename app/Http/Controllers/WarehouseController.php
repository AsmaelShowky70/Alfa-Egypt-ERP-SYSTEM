<?php

namespace App\Http\Controllers;

use App\Models\DailyInventoryCheck;
use App\Models\DailyInventoryCheckItem;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionItem;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $rawMaterials = RawMaterial::where('category', '!=', 'spare_part')->get();
        $spareParts = RawMaterial::where('category', 'spare_part')->get();
        $finishedGoods = Product::with('customer')->get();

        $rawMaterialsValue = $rawMaterials->sum(fn($i) => $i->current_stock * $i->unit_cost);
        $finishedGoodsValue = $finishedGoods->sum(fn($i) => $i->current_stock * $i->cost_price);
        $sparePartsValue = $spareParts->sum(fn($i) => $i->current_stock * $i->unit_cost);

        $lowStockMaterials = RawMaterial::whereColumn('current_stock', '<=', 'reorder_level')->get();
        $pendingRequisitions = MaterialRequisition::with(['productionOrder.product', 'items.rawMaterial'])
            ->where('status', 'requested')
            ->latest()
            ->get();

        $recentVouchers = StockMovement::with(['warehouseManager', 'items'])
            ->latest()
            ->take(10)
            ->get();

        return view('warehouse.index', compact(
            'rawMaterials',
            'spareParts',
            'finishedGoods',
            'rawMaterialsValue',
            'finishedGoodsValue',
            'sparePartsValue',
            'lowStockMaterials',
            'pendingRequisitions',
            'recentVouchers'
        ));
    }

    // ================= INVENTORY LISTS =================
    public function rawMaterials(): View
    {
        $materials = RawMaterial::with(['supplier', 'sourceFactory'])->latest()->paginate(20);
        $suppliers = Supplier::where('status', true)->get();
        return view('warehouse.raw_materials', compact('materials', 'suppliers'));
    }

    public function finishedGoods(): View
    {
        $products = Product::with(['customer', 'factory', 'boms.rawMaterial'])->latest()->paginate(20);
        return view('warehouse.finished_goods', compact('products'));
    }

    public function spareParts(): View
    {
        $spareParts = RawMaterial::where('category', 'spare_part')->with('supplier')->latest()->paginate(20);
        $suppliers = Supplier::where('category', 'قطع غيار')->orWhere('status', true)->get();
        return view('warehouse.spare_parts', compact('spareParts', 'suppliers'));
    }

    // ================= MATERIAL REQUISITIONS FROM OPERATIONS =================
    public function materialRequisitions(): View
    {
        $requisitions = MaterialRequisition::with(['productionOrder.salesOrder.customer', 'productionOrder.product', 'items.rawMaterial', 'requestedByUser'])
            ->latest()
            ->paginate(15);
        return view('warehouse.material_requisitions.index', compact('requisitions'));
    }

    public function showMaterialRequisition(MaterialRequisition $materialRequisition): View
    {
        $materialRequisition->load([
            'productionOrder.salesOrder.customer',
            'productionOrder.product',
            'items.rawMaterial',
            'requestedByUser',
            'issuedByUser'
        ]);

        return view('warehouse.material_requisitions.show', compact('materialRequisition'));
    }

    /**
     * Issue materials from central warehouse to production
     */
    public function issueRequisition(Request $request, MaterialRequisition $materialRequisition): RedirectResponse
    {
        $materialRequisition->load('items.rawMaterial', 'productionOrder');

        DB::beginTransaction();
        try {
            $voucherNumber = 'VOUCH-ISSUE-' . date('Ymd') . '-' . str_pad((StockMovement::count() + 1), 3, '0', STR_PAD_LEFT);
            $totalValue = 0;

            $voucher = StockMovement::create([
                'voucher_number' => $voucherNumber,
                'type' => 'issue_production',
                'reference_type' => 'production_order',
                'reference_id' => $materialRequisition->production_order_id,
                'warehouse_manager_id' => Auth::id(),
                'voucher_date' => Carbon::now()->toDateString(),
                'total_value' => 0,
                'received_or_delivered_by' => $materialRequisition->productionOrder->operationsManager->name ?? 'مندوب قسم التشغيل',
                'is_archived' => false,
                'notes' => 'صرف مستلزمات إنتاج لأمر التشغيل ' . $materialRequisition->productionOrder->order_number,
            ]);

            $hasMissing = false;

            foreach ($materialRequisition->items as $item) {
                $rm = $item->rawMaterial;
                $requested = (float)$item->quantity_requested;
                $available = (float)$rm->current_stock;

                $toIssue = min($requested, $available);
                $missing = max(0, $requested - $available);

                if ($missing > 0) {
                    $hasMissing = true;
                }

                // Deduct from stock
                $rm->current_stock = max(0, $available - $toIssue);
                $rm->save();

                $item->update([
                    'quantity_issued' => $toIssue,
                    'quantity_missing' => $missing,
                ]);

                $sub = $toIssue * $item->unit_cost;
                $totalValue += $sub;

                StockMovementItem::create([
                    'stock_movement_id' => $voucher->id,
                    'item_type' => 'raw_material',
                    'item_id' => $rm->id,
                    'item_name' => $rm->name,
                    'unit' => $rm->unit,
                    'quantity' => $toIssue,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $sub,
                    'notes' => 'صرف لصالح أمر تشغيل ' . $materialRequisition->productionOrder->order_number,
                ]);
            }

            $voucher->update(['total_value' => $totalValue]);

            $materialRequisition->update([
                'status' => $hasMissing ? 'partial_issued' : 'fully_issued',
                'issued_by' => Auth::id(),
                'issued_at' => now(),
            ]);

            DB::commit();

            $msg = 'تم إصدار إذن صرف المخزن رقم ' . $voucherNumber . ' بنجاح ويمكن طباعته الآن.';
            if ($hasMissing) {
                $msg .= ' ⚠️ تنبيه: تم صرف الكميات المتاحة فقط، وهناك نواقص يلزم إصدار طلب شراء لها.';
            }

            return redirect()->route('warehouse.vouchers.show', $voucher->id)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الصرف: ' . $e->getMessage());
        }
    }

    /**
     * Forward missing items from Material Requisition to Purchasing department
     */
    public function createPurchaseRequisitionFromMissing(Request $request, MaterialRequisition $materialRequisition): RedirectResponse
    {
        $materialRequisition->load('items.rawMaterial', 'productionOrder.salesOrder');

        $missingItems = $materialRequisition->items->filter(fn($i) => $i->quantity_missing > 0);
        if ($missingItems->isEmpty()) {
            return back()->with('info', 'لا توجد خامات ناقصة في هذا الطلب.');
        }

        $prNumber = 'PR-' . date('Y') . '-' . str_pad((PurchaseRequisition::count() + 1), 3, '0', STR_PAD_LEFT);

        $pr = PurchaseRequisition::create([
            'pr_number' => $prNumber,
            'warehouse_manager_id' => Auth::id(),
            'sales_order_id' => $materialRequisition->productionOrder->sales_order_id,
            'production_order_id' => $materialRequisition->production_order_id,
            'status' => 'pending_procurement',
            'procurement_lead_time_days' => 0,
            'urgency' => 'critical',
            'reason' => 'خامات ناقصة بمخزن المصنع لإنتاج طلب العميل ' . ($materialRequisition->productionOrder->salesOrder->customer->name ?? ''),
        ]);

        foreach ($missingItems as $item) {
            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $pr->id,
                'raw_material_id' => $item->raw_material_id,
                'needed_quantity' => $item->quantity_missing,
                'current_stock_at_request' => $item->rawMaterial->current_stock,
                'supplier_id' => $item->rawMaterial->supplier_id,
                'estimated_unit_price' => $item->rawMaterial->unit_cost,
                'lead_time_days' => 0,
                'notes' => 'طلب عاجل من المخزن المركزي للتشغيل',
            ]);
        }

        return redirect()->route('purchasing.requisitions.show', $pr->id)
            ->with('success', 'تم إنشاء طلب الشراء بالنواقص رقم ' . $prNumber . ' وتوجيهه فوراً لمدير المشتريات مؤمن عاطف للرد بالمدة المتاحة للتوريد.');
    }

    // ================= STOCK MOVEMENTS & VOUCHERS =================
    public function vouchers(): View
    {
        $vouchers = StockMovement::with(['warehouseManager', 'items'])->latest()->paginate(20);
        return view('warehouse.vouchers.index', compact('vouchers'));
    }

    public function showVoucher(StockMovement $voucher): View
    {
        $voucher->load(['warehouseManager', 'items']);
        return view('warehouse.vouchers.show', compact('voucher'));
    }

    public function createVoucher(): View
    {
        $rawMaterials = RawMaterial::where('status', true)->get();
        $products = Product::where('status', true)->get();
        return view('warehouse.vouchers.create', compact('rawMaterials', 'products'));
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'voucher_date' => 'required|date',
            'received_or_delivered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:raw_material,product',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $voucherNumber = 'VOUCH-' . date('Ymd') . '-' . str_pad((StockMovement::count() + 1), 3, '0', STR_PAD_LEFT);
            $totalValue = 0;

            $voucher = StockMovement::create([
                'voucher_number' => $voucherNumber,
                'type' => $validated['type'],
                'warehouse_manager_id' => Auth::id(),
                'voucher_date' => $validated['voucher_date'],
                'total_value' => 0,
                'received_or_delivered_by' => $validated['received_or_delivered_by'],
                'is_archived' => false,
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = (float)$itemData['quantity'];
                $cost = (float)$itemData['unit_cost'];
                $sub = $qty * $cost;
                $totalValue += $sub;

                $itemName = '';
                $unit = 'قطعة';

                if ($itemData['item_type'] === 'raw_material') {
                    $rm = RawMaterial::findOrFail($itemData['item_id']);
                    $itemName = $rm->name;
                    $unit = $rm->unit;

                    // Update stock based on type
                    if (str_starts_with($validated['type'], 'receipt')) {
                        $rm->current_stock += $qty;
                    } elseif (str_starts_with($validated['type'], 'issue') || $validated['type'] === 'sell_to_third_party') {
                        $rm->current_stock = max(0, $rm->current_stock - $qty);
                    }
                    $rm->save();
                } else {
                    $p = Product::findOrFail($itemData['item_id']);
                    $itemName = $p->name;
                    $unit = $p->unit;

                    if (str_starts_with($validated['type'], 'receipt')) {
                        $p->current_stock += $qty;
                    } elseif (str_starts_with($validated['type'], 'delivery') || $validated['type'] === 'sell_to_third_party') {
                        $p->current_stock = max(0, $p->current_stock - $qty);
                    }
                    $p->save();
                }

                StockMovementItem::create([
                    'stock_movement_id' => $voucher->id,
                    'item_type' => $itemData['item_type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemName,
                    'unit' => $unit,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_cost' => $sub,
                ]);
            }

            $voucher->update(['total_value' => $totalValue]);

            DB::commit();
            return redirect()->route('warehouse.vouchers.show', $voucher->id)->with('success', 'تم حفظ السند بنجاح ويمكن طباعته وأرشفته.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ أثناء حفظ السند: ' . $e->getMessage());
        }
    }

    // ================= DAILY INVENTORY CHECK (الجرد اليومي) =================
    public function dailyChecks(): View
    {
        $checks = DailyInventoryCheck::with(['warehouseManager', 'items'])->latest()->paginate(20);
        return view('warehouse.daily_checks.index', compact('checks'));
    }

    public function createDailyCheck(Request $request): View
    {
        $category = $request->get('category', 'raw_materials');
        if ($category === 'raw_materials') {
            $items = RawMaterial::where('category', '!=', 'spare_part')->get();
        } elseif ($category === 'spare_parts') {
            $items = RawMaterial::where('category', 'spare_part')->get();
        } else {
            $items = Product::all();
        }

        return view('warehouse.daily_checks.create', compact('category', 'items'));
    }

    public function storeDailyCheck(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'check_date' => 'required|date',
            'category' => 'required|in:raw_materials,finished_goods,spare_parts',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.item_type' => 'required|in:raw_material,product',
            'items.*.system_qty' => 'required|numeric',
            'items.*.actual_qty' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric',
            'items.*.reason' => 'nullable|string',
        ]);

        $checkCode = 'INV-' . str_replace('-', '', $validated['check_date']) . '-' . str_pad((DailyInventoryCheck::count() + 1), 2, '0', STR_PAD_LEFT);

        $check = DailyInventoryCheck::create([
            'check_code' => $checkCode,
            'check_date' => $validated['check_date'],
            'warehouse_manager_id' => Auth::id(),
            'category' => $validated['category'],
            'status' => 'approved',
            'total_surplus_value' => 0,
            'total_deficit_value' => 0,
            'notes' => $validated['notes'],
        ]);

        $totalSurplus = 0;
        $totalDeficit = 0;

        foreach ($validated['items'] as $itemData) {
            $sys = (float)$itemData['system_qty'];
            $act = (float)$itemData['actual_qty'];
            $diff = $act - $sys;
            $cost = (float)$itemData['unit_cost'];
            $impact = $diff * $cost;

            if ($diff > 0) {
                $totalSurplus += $impact;
            } else {
                $totalDeficit += abs($impact);
            }

            $name = '';
            $unit = 'قطعة';
            if ($itemData['item_type'] === 'raw_material') {
                $rm = RawMaterial::find($itemData['item_id']);
                $name = $rm->name ?? '';
                $unit = $rm->unit ?? 'قطعة';
                // Adjust system stock to actual
                if ($rm) {
                    $rm->update(['current_stock' => $act]);
                }
            } else {
                $p = Product::find($itemData['item_id']);
                $name = $p->name ?? '';
                $unit = $p->unit ?? 'قطعة';
                if ($p) {
                    $p->update(['current_stock' => $act]);
                }
            }

            DailyInventoryCheckItem::create([
                'daily_inventory_check_id' => $check->id,
                'item_type' => $itemData['item_type'],
                'item_id' => $itemData['item_id'],
                'item_name' => $name,
                'unit' => $unit,
                'system_quantity' => $sys,
                'actual_quantity' => $act,
                'difference' => $diff,
                'unit_cost' => $cost,
                'financial_impact' => $impact,
                'reason' => $itemData['reason'] ?? null,
            ]);
        }

        $check->update([
            'total_surplus_value' => $totalSurplus,
            'total_deficit_value' => $totalDeficit,
        ]);

        return redirect()->route('warehouse.dailyChecks.show', $check->id)
            ->with('success', 'تم اعتماد الجرد اليومي رقم ' . $checkCode . ' وتحديث أرصدة المخزن الفعلى بنجاح.');
    }

    public function showDailyCheck(DailyInventoryCheck $dailyCheck): View
    {
        $dailyCheck->load(['warehouseManager', 'items']);
        return view('warehouse.daily_checks.show', compact('dailyCheck'));
    }
}
