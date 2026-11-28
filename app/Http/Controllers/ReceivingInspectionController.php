<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\ReceivingInspection;
use App\Models\ReceivingInspectionItem;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivingInspectionController extends Controller
{
    public function index(): View
    {
        $inspections = ReceivingInspection::with(['supplier', 'factory', 'warehouseManager', 'items'])
            ->latest()
            ->paginate(15);

        return view('warehouse.inspections.index', compact('inspections'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', true)->orderBy('name')->get();
        $factories = Factory::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with(['supplier', 'fromFactory'])
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->take(30)
            ->get();
        $rawMaterials = RawMaterial::where('status', true)->orderBy('name')->get();
        $products = Product::where('status', true)->orderBy('name')->get();

        $rawMaterialsJson = $rawMaterials->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'unit' => $r->unit,
            'cat' => $r->category,
        ])->values();

        $productsJson = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'unit' => $p->unit,
        ])->values();

        return view('warehouse.inspections.create', compact(
            'suppliers',
            'factories',
            'purchaseOrders',
            'rawMaterialsJson',
            'productsJson'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'source_type' => 'required|in:supplier,internal_factory,other',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'factory_id' => 'nullable|exists:factories,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'delivered_by' => 'nullable|string|max:255',
            'inspector_name' => 'required|string|max:255',
            'quality_member_name' => 'nullable|string|max:255',
            'add_to_stock' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:raw_material,spare_part,product',
            'items.*.item_id' => 'required|integer',
            'items.*.delivered_quantity' => 'required|numeric|min:0.01',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $addToStock = (bool) ($validated['add_to_stock'] ?? false);

        DB::beginTransaction();
        try {
            $nextNum = (int) (ReceivingInspection::max('id') ?? 0) + 1;
            do {
                $inspNumber = 'INSP-'.date('Y').'-'.str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                $nextNum++;
            } while (ReceivingInspection::where('inspection_number', $inspNumber)->exists());

            $hasAccepted = false;
            $hasRejected = false;
            $preparedItems = [];

            foreach ($validated['items'] as $row) {
                $delivered = (float) $row['delivered_quantity'];
                $accepted = min($delivered, (float) $row['accepted_quantity']);
                $rejected = max(0, $delivered - $accepted);

                if ($accepted > 0) {
                    $hasAccepted = true;
                }
                if ($rejected > 0) {
                    $hasRejected = true;
                }

                $result = $accepted <= 0 ? 'rejected' : ($rejected > 0 ? 'partial' : 'accepted');

                if ($row['item_type'] === 'product') {
                    $model = Product::findOrFail($row['item_id']);
                    $cost = (float) $model->cost_price;
                } else {
                    $model = RawMaterial::findOrFail($row['item_id']);
                    $cost = (float) $model->unit_cost;
                }

                $preparedItems[] = [
                    'model' => $model,
                    'item_type' => $row['item_type'],
                    'item_id' => $model->id,
                    'item_name' => $model->name,
                    'unit' => $model->unit,
                    'unit_cost' => $cost,
                    'delivered_quantity' => $delivered,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'inspection_result' => $result,
                    'notes' => $row['notes'] ?? null,
                ];
            }

            $overallStatus = ! $hasAccepted ? 'rejected' : ($hasRejected ? 'partial' : 'accepted');

            $stockMovementId = null;
            if ($addToStock && $hasAccepted) {
                $vNext = (int) (StockMovement::max('id') ?? 0) + 1;
                do {
                    $vNum = 'WH-RCV-'.date('Y').'-'.str_pad($vNext, 3, '0', STR_PAD_LEFT);
                    $vNext++;
                } while (StockMovement::where('voucher_number', $vNum)->exists());

                $totalValue = 0;
                foreach ($preparedItems as $pi) {
                    $totalValue += $pi['accepted_quantity'] * $pi['unit_cost'];
                }

                $movement = StockMovement::create([
                    'voucher_number' => $vNum,
                    'type' => $validated['source_type'] === 'internal_factory' ? 'receipt_factory_internal' : 'receipt_supplier',
                    'reference_type' => $validated['source_type'] === 'internal_factory' ? 'factory' : ($validated['source_type'] === 'supplier' ? 'supplier' : 'inspection'),
                    'reference_id' => $validated['source_type'] === 'internal_factory' ? ($validated['factory_id'] ?? null) : ($validated['supplier_id'] ?? null),
                    'warehouse_manager_id' => Auth::id(),
                    'voucher_date' => $validated['inspection_date'],
                    'total_value' => $totalValue,
                    'received_or_delivered_by' => $validated['delivered_by'] ?? $validated['inspector_name'],
                    'notes' => 'إضافة مخزنية آلية بموجب محضر فحص واستلام رقم '.$inspNumber,
                ]);

                foreach ($preparedItems as $pi) {
                    if ($pi['accepted_quantity'] <= 0) {
                        continue;
                    }
                    StockMovementItem::create([
                        'stock_movement_id' => $movement->id,
                        'item_type' => $pi['item_type'] === 'product' ? 'product' : 'raw_material',
                        'item_id' => $pi['item_id'],
                        'item_name' => $pi['item_name'],
                        'quantity' => $pi['accepted_quantity'],
                        'unit' => $pi['unit'],
                        'unit_cost' => $pi['unit_cost'],
                        'total_cost' => $pi['accepted_quantity'] * $pi['unit_cost'],
                    ]);

                    $pi['model']->increment('current_stock', $pi['accepted_quantity']);
                }

                $stockMovementId = $movement->id;
            }

            $inspection = ReceivingInspection::create([
                'inspection_number' => $inspNumber,
                'inspection_date' => $validated['inspection_date'],
                'source_type' => $validated['source_type'],
                'supplier_id' => $validated['source_type'] === 'supplier' ? ($validated['supplier_id'] ?? null) : null,
                'factory_id' => $validated['source_type'] === 'internal_factory' ? ($validated['factory_id'] ?? null) : null,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'delivered_by' => $validated['delivered_by'] ?? null,
                'inspector_name' => $validated['inspector_name'],
                'quality_member_name' => $validated['quality_member_name'] ?? null,
                'overall_status' => $overallStatus,
                'add_to_stock' => $addToStock && $hasAccepted,
                'stock_movement_id' => $stockMovementId,
                'warehouse_manager_id' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($preparedItems as $pi) {
                ReceivingInspectionItem::create([
                    'receiving_inspection_id' => $inspection->id,
                    'item_type' => $pi['item_type'],
                    'item_id' => $pi['item_id'],
                    'item_name' => $pi['item_name'],
                    'unit' => $pi['unit'],
                    'delivered_quantity' => $pi['delivered_quantity'],
                    'accepted_quantity' => $pi['accepted_quantity'],
                    'rejected_quantity' => $pi['rejected_quantity'],
                    'inspection_result' => $pi['inspection_result'],
                    'notes' => $pi['notes'],
                ]);
            }

            DB::commit();

            return redirect()->route('warehouse.inspections.show', $inspection->id)
                ->with('success', 'تم اعتماد محضر الفحص والاستلام رقم '.$inspNumber.' بنجاح'.($stockMovementId ? ' وترحيل الكميات المقبولة للمخزن المركزي.' : '.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ محضر الفحص: '.$e->getMessage());
        }
    }

    public function show(ReceivingInspection $inspection): View
    {
        $inspection->load(['supplier', 'factory', 'purchaseOrder', 'stockMovement', 'warehouseManager', 'items']);

        return view('warehouse.inspections.show', compact('inspection'));
    }
}
