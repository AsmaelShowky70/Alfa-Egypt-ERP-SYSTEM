<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyInventoryCheck;
use App\Models\DailyInventoryCheckItem;
use App\Models\Factory;
use App\Models\MaterialRequisition;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use App\Models\WarehouseArchivedReport;
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

        $rawMaterialsValue = $rawMaterials->sum(fn ($i) => $i->current_stock * $i->unit_cost);
        $finishedGoodsValue = $finishedGoods->sum(fn ($i) => $i->current_stock * $i->cost_price);
        $sparePartsValue = $spareParts->sum(fn ($i) => $i->current_stock * $i->unit_cost);

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
            'issuedByUser',
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
            $voucherNumber = 'VOUCH-ISSUE-'.date('Ymd').'-'.str_pad((StockMovement::count() + 1), 3, '0', STR_PAD_LEFT);
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
                'notes' => 'صرف مستلزمات إنتاج لأمر التشغيل '.$materialRequisition->productionOrder->order_number,
            ]);

            $hasMissing = false;

            foreach ($materialRequisition->items as $item) {
                $rm = $item->rawMaterial;
                $requested = (float) $item->quantity_requested;
                $available = (float) $rm->current_stock;

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
                    'notes' => 'صرف لصالح أمر تشغيل '.$materialRequisition->productionOrder->order_number,
                ]);
            }

            $voucher->update(['total_value' => $totalValue]);

            $materialRequisition->update([
                'status' => $hasMissing ? 'partial_issued' : 'fully_issued',
                'issued_by' => Auth::id(),
                'issued_at' => now(),
            ]);

            DB::commit();

            $msg = 'تم إصدار إذن صرف المخزن رقم '.$voucherNumber.' بنجاح ويمكن طباعته الآن.';
            if ($hasMissing) {
                $msg .= ' ⚠️ تنبيه: تم صرف الكميات المتاحة فقط، وهناك نواقص يلزم إصدار طلب شراء لها.';
            }

            return redirect()->route('warehouse.vouchers.show', $voucher->id)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء الصرف: '.$e->getMessage());
        }
    }

    /**
     * Forward missing items from Material Requisition to Purchasing department
     */
    public function createPurchaseRequisitionFromMissing(Request $request, MaterialRequisition $materialRequisition): RedirectResponse
    {
        $materialRequisition->load('items.rawMaterial', 'productionOrder.salesOrder');

        $missingItems = $materialRequisition->items->filter(fn ($i) => $i->quantity_missing > 0);
        if ($missingItems->isEmpty()) {
            return back()->with('info', 'لا توجد خامات ناقصة في هذا الطلب.');
        }

        $prNumber = 'PR-'.date('Y').'-'.str_pad((PurchaseRequisition::count() + 1), 3, '0', STR_PAD_LEFT);

        $pr = PurchaseRequisition::create([
            'pr_number' => $prNumber,
            'warehouse_manager_id' => Auth::id(),
            'sales_order_id' => $materialRequisition->productionOrder->sales_order_id,
            'production_order_id' => $materialRequisition->production_order_id,
            'status' => 'pending_procurement',
            'procurement_lead_time_days' => 0,
            'urgency' => 'critical',
            'reason' => 'خامات ناقصة بمخزن المصنع لإنتاج طلب العميل '.($materialRequisition->productionOrder->salesOrder->customer->name ?? ''),
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
            ->with('success', 'تم إنشاء طلب الشراء بالنواقص رقم '.$prNumber.' وتوجيهه فوراً لمدير المشتريات مؤمن عاطف للرد بالمدة المتاحة للتوريد.');
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
            $voucherNumber = 'VOUCH-'.date('Ymd').'-'.str_pad((StockMovement::count() + 1), 3, '0', STR_PAD_LEFT);
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
                $qty = (float) $itemData['quantity'];
                $cost = (float) $itemData['unit_cost'];
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

            return back()->with('error', 'خطأ أثناء حفظ السند: '.$e->getMessage());
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

        $checkCode = 'INV-'.str_replace('-', '', $validated['check_date']).'-'.str_pad((DailyInventoryCheck::count() + 1), 2, '0', STR_PAD_LEFT);

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
            $sys = (float) $itemData['system_qty'];
            $act = (float) $itemData['actual_qty'];
            $diff = $act - $sys;
            $cost = (float) $itemData['unit_cost'];
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
            ->with('success', 'تم اعتماد الجرد اليومي رقم '.$checkCode.' وتحديث أرصدة المخزن الفعلى بنجاح.');
    }

    public function showDailyCheck(DailyInventoryCheck $dailyCheck): View
    {
        $dailyCheck->load(['warehouseManager', 'items']);

        return view('warehouse.daily_checks.show', compact('dailyCheck'));
    }

    // ================= WAREHOUSE 8-IN-1 REPORTS & ARCHIVAL SYSTEM =================

    public function reports(Request $request): View
    {
        $reportType = $request->input('report_type', 'all');
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());
        $rawMaterialId = $request->input('raw_material_id');
        $productId = $request->input('product_id');
        $supplierId = $request->input('supplier_id');
        $customerId = $request->input('customer_id');
        $factoryId = $request->input('factory_id');

        $rawMaterials = RawMaterial::orderBy('name')->get();
        $products = Product::with('customer')->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $factories = Factory::orderBy('id')->get();

        $query = $this->buildReportsQuery($request);

        $totalQuantity = (clone $query)->sum('quantity');
        $totalValue = (clone $query)->sum('total_cost');
        $itemsCount = (clone $query)->count();

        $movements = (clone $query)->latest('id')->paginate(25)->withQueryString();

        $meta = $this->getReportMeta($reportType, $request);

        return view('warehouse.reports.index', compact(
            'reportType',
            'fromDate',
            'toDate',
            'rawMaterialId',
            'productId',
            'supplierId',
            'customerId',
            'factoryId',
            'rawMaterials',
            'products',
            'suppliers',
            'customers',
            'factories',
            'movements',
            'totalQuantity',
            'totalValue',
            'itemsCount',
            'meta'
        ));
    }

    public function printReport(Request $request): View
    {
        $reportType = $request->input('report_type', 'all');
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());

        $query = $this->buildReportsQuery($request);
        $items = $query->latest('id')->get();

        $totalQuantity = $items->sum('quantity');
        $totalValue = $items->sum('total_cost');
        $itemsCount = $items->count();

        $meta = $this->getReportMeta($reportType, $request);

        return view('print.warehouse_report', compact(
            'reportType',
            'fromDate',
            'toDate',
            'items',
            'totalQuantity',
            'totalValue',
            'itemsCount',
            'meta'
        ));
    }

    public function archiveReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|string',
            'title' => 'required|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'item_type' => 'nullable|string',
            'item_id' => 'nullable|integer',
            'item_name' => 'nullable|string',
            'party_type' => 'nullable|string',
            'party_id' => 'nullable|integer',
            'party_name' => 'nullable|string',
            'total_quantity' => 'nullable|numeric',
            'total_value' => 'nullable|numeric',
            'warehouse_manager_signature' => 'nullable|string',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_title' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'recipient_signature' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $query = $this->buildReportsQuery($request);
        $rawItems = $query->latest('id')->get();

        $snapshotItems = $rawItems->map(function ($it) {
            return [
                'id' => $it->id,
                'voucher_number' => $it->stockMovement?->voucher_number,
                'voucher_date' => $it->stockMovement?->voucher_date?->format('Y-m-d'),
                'item_name' => $it->item_name,
                'quantity' => (float) $it->quantity,
                'unit' => $it->unit,
                'unit_cost' => (float) $it->unit_cost,
                'total_cost' => (float) $it->total_cost,
                'party_name' => $it->stockMovement?->party_display_name,
                'notes' => $it->notes ?? $it->stockMovement?->notes,
            ];
        })->toArray();

        $todayCode = date('Ymd');
        $seq = WarehouseArchivedReport::whereDate('created_at', today())->count() + 1;
        $reportNumber = 'WH-REP-'.$todayCode.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

        $archived = WarehouseArchivedReport::create([
            'report_number' => $reportNumber,
            'report_type' => $validated['report_type'],
            'title' => $validated['title'],
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'item_type' => $validated['item_type'] ?? null,
            'item_id' => $validated['item_id'] ?? null,
            'item_name' => $validated['item_name'] ?? null,
            'party_type' => $validated['party_type'] ?? null,
            'party_id' => $validated['party_id'] ?? null,
            'party_name' => $validated['party_name'] ?? null,
            'total_quantity' => $validated['total_quantity'] ?? $rawItems->sum('quantity'),
            'total_value' => $validated['total_value'] ?? $rawItems->sum('total_cost'),
            'items_count' => count($snapshotItems),
            'data_payload' => [
                'filters' => $request->all(),
                'items' => $snapshotItems,
            ],
            'warehouse_manager_id' => Auth::id() ?? 3,
            'warehouse_manager_signature' => $validated['warehouse_manager_signature'] ?? null,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'recipient_title' => $validated['recipient_title'] ?? null,
            'recipient_phone' => $validated['recipient_phone'] ?? null,
            'recipient_signature' => $validated['recipient_signature'] ?? null,
            'is_signed_by_recipient' => ! empty($validated['recipient_signature']),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('warehouse.reports.showArchived', $archived->id)
            ->with('success', 'تم اعتماد وأرشفة التقرير المخزني بنجاح تحت رقم ['.$reportNumber.'] وتوثيق التوقيعات إلكترونياً.');
    }

    public function archivedReports(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('report_type');

        $query = WarehouseArchivedReport::with('warehouseManager')->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('party_name', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        if ($type && $type !== 'all') {
            $query->where('report_type', $type);
        }

        $archivedReports = $query->paginate(20)->withQueryString();

        return view('warehouse.reports.archived', compact('archivedReports', 'search', 'type'));
    }

    public function showArchivedReport(WarehouseArchivedReport $archivedReport): View
    {
        $archivedReport->load('warehouseManager');

        return view('warehouse.reports.show_archived', compact('archivedReport'));
    }

    public function storeFactory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:factories,code',
            'type' => 'required|in:internal,outsourced',
            'supervisor_name' => 'nullable|string|max:255',
            'operating_cost_per_hour' => 'nullable|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $factory = Factory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'supervisor_name' => $validated['supervisor_name'] ?? 'مدير تشغيل المصنع',
            'operating_cost_per_hour' => $validated['operating_cost_per_hour'] ?? 0,
            'markup_percentage' => $validated['markup_percentage'] ?? ($validated['type'] === 'internal' ? 14 : 0),
            'status' => true,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'تم تثبيت وإضافة المصنع الجديد ['.$factory->name.'] بنجاح إلى منظومة المصانع.');
    }

    // ================= PRIVATE QUERY HELPERS FOR WAREHOUSE REPORTS =================

    private function buildReportsQuery(Request $request)
    {
        $reportType = $request->input('report_type', 'all');
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());
        $rawMaterialId = $request->input('raw_material_id');
        $productId = $request->input('product_id');
        $supplierId = $request->input('supplier_id');
        $customerId = $request->input('customer_id');
        $factoryId = $request->input('factory_id');

        $query = StockMovementItem::with(['stockMovement.warehouseManager'])
            ->whereHas('stockMovement', function ($q) use ($fromDate, $toDate) {
                if ($fromDate) {
                    $q->whereDate('voucher_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('voucher_date', '<=', $toDate);
                }
            });

        switch ($reportType) {
            case 'raw_receipt_by_item': // 1. إجمالي استلام صنف خامات
                $query->where('item_type', 'raw_material')
                    ->whereHas('stockMovement', fn ($q) => $q->whereIn('type', ['receipt_supplier', 'receipt_factory_internal']));
                if ($rawMaterialId) {
                    $query->where('item_id', $rawMaterialId);
                }
                break;

            case 'raw_issue_by_item': // 2. إجمالي صرف صنف خامات
                $query->where('item_type', 'raw_material')
                    ->whereHas('stockMovement', fn ($q) => $q->where('type', 'issue_production'));
                if ($rawMaterialId) {
                    $query->where('item_id', $rawMaterialId);
                }
                break;

            case 'finished_receipt_production': // 3. إجمالي استلام منتج تام من الإنتاج
                $query->where('item_type', 'product')
                    ->whereHas('stockMovement', fn ($q) => $q->where('type', 'receipt_factory_internal'));
                if ($productId) {
                    $query->where('item_id', $productId);
                }
                break;

            case 'finished_delivery_customer': // 4. إجمالي تسليم منتج تام إلى العميل (س)
                $query->where('item_type', 'product')
                    ->whereHas('stockMovement', function ($q) use ($customerId) {
                        $q->where('type', 'delivery_customer');
                        if ($customerId) {
                            $q->where('reference_type', 'customer')->where('reference_id', $customerId);
                        }
                    });
                if ($productId) {
                    $query->where('item_id', $productId);
                }
                break;

            case 'raw_receipt_supplier': // 5. إجمالي استلام خامات من المورد (س)
                $query->where('item_type', 'raw_material')
                    ->whereHas('stockMovement', function ($q) use ($supplierId) {
                        $q->where('type', 'receipt_supplier');
                        if ($supplierId) {
                            $q->where('reference_type', 'supplier')->where('reference_id', $supplierId);
                        }
                    });
                if ($rawMaterialId) {
                    $query->where('item_id', $rawMaterialId);
                }
                break;

            case 'raw_receipt_factories': // 7. إجمالي استلام صنف خامات من أي مصنع من الثلاثة
                $query->where('item_type', 'raw_material')
                    ->whereHas('stockMovement', function ($q) use ($factoryId) {
                        $q->whereIn('type', ['receipt_factory_internal', 'receipt_supplier'])
                            ->where('reference_type', 'factory');
                        if ($factoryId) {
                            $q->where('reference_id', $factoryId);
                        }
                    });
                if ($rawMaterialId) {
                    $query->where('item_id', $rawMaterialId);
                }
                break;

            case 'raw_issue_factories': // 8. إجمالي تسليم صنف خامات إلى أي مصنع من الثلاثة
                $query->where('item_type', 'raw_material')
                    ->whereHas('stockMovement', function ($q) use ($factoryId) {
                        $q->where('type', 'issue_production')
                            ->where('reference_type', 'factory');
                        if ($factoryId) {
                            $q->where('reference_id', $factoryId);
                        }
                    });
                if ($rawMaterialId) {
                    $query->where('item_id', $rawMaterialId);
                }
                break;

            case 'all':
            default:
                if ($rawMaterialId) {
                    $query->where('item_type', 'raw_material')->where('item_id', $rawMaterialId);
                }
                if ($productId) {
                    $query->where('item_type', 'product')->where('item_id', $productId);
                }
                if ($supplierId) {
                    $query->whereHas('stockMovement', fn ($q) => $q->where('reference_type', 'supplier')->where('reference_id', $supplierId));
                }
                if ($customerId) {
                    $query->whereHas('stockMovement', fn ($q) => $q->where('reference_type', 'customer')->where('reference_id', $customerId));
                }
                if ($factoryId) {
                    $query->whereHas('stockMovement', fn ($q) => $q->where('reference_type', 'factory')->where('reference_id', $factoryId));
                }
                break;
        }

        return $query;
    }

    private function getReportMeta(string $reportType, Request $request): array
    {
        $rawMaterialId = $request->input('raw_material_id');
        $productId = $request->input('product_id');
        $supplierId = $request->input('supplier_id');
        $customerId = $request->input('customer_id');
        $factoryId = $request->input('factory_id');

        $itemName = null;
        $itemType = null;
        if ($rawMaterialId) {
            $item = RawMaterial::find($rawMaterialId);
            $itemName = $item?->name;
            $itemType = 'raw_material';
        } elseif ($productId) {
            $item = Product::find($productId);
            $itemName = $item?->name;
            $itemType = 'product';
        }

        $partyName = null;
        $partyType = null;
        $partyId = null;
        if ($supplierId) {
            $sup = Supplier::find($supplierId);
            $partyName = $sup?->name;
            $partyType = 'supplier';
            $partyId = $supplierId;
        } elseif ($customerId) {
            $cust = Customer::find($customerId);
            $partyName = $cust?->name;
            $partyType = 'customer';
            $partyId = $customerId;
        } elseif ($factoryId) {
            $fac = Factory::find($factoryId);
            $partyName = $fac?->name;
            $partyType = 'factory';
            $partyId = $factoryId;
        }

        $title = match ($reportType) {
            'raw_receipt_by_item' => 'إجمالي استلام صنف خامات'.($itemName ? " ({$itemName})" : ''),
            'raw_issue_by_item' => 'إجمالي صرف صنف خامات للتشغيل'.($itemName ? " ({$itemName})" : ''),
            'finished_receipt_production' => 'إجمالي استلام منتج تام من الإنتاج'.($itemName ? " ({$itemName})" : ''),
            'finished_delivery_customer' => 'إجمالي تسليم منتج تام إلى العميل'.($partyName ? " ({$partyName})" : ''),
            'raw_receipt_supplier' => 'إجمالي استلام خامات من المورد'.($partyName ? " ({$partyName})" : ''),
            'raw_receipt_factories' => 'إجمالي استلام خامات من المصانع'.($partyName ? " ({$partyName})" : ' (أوتوباك / بلينز باك / مصطفى قطين)'),
            'raw_issue_factories' => 'إجمالي تسليم خامات إلى المصانع'.($partyName ? " ({$partyName})" : ' (أوتوباك / بلينز باك / مصطفى قطين)'),
            default => 'تقرير شامل لحركة الأصناف والمخزون',
        };

        return [
            'title' => $title,
            'report_type' => $reportType,
            'item_name' => $itemName,
            'item_type' => $itemType,
            'item_id' => $rawMaterialId ?? $productId,
            'party_name' => $partyName,
            'party_type' => $partyType,
            'party_id' => $partyId,
        ];
    }
}
