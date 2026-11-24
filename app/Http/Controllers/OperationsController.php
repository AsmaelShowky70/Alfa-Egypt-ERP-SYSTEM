<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionItem;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function index(): View
    {
        $pendingSalesOrders = SalesOrder::with(['customer', 'items.product.boms.rawMaterial'])
            ->whereIn('status', ['pending_operations', 'procurement_delayed'])
            ->latest()
            ->get();

        $activeProductionOrders = ProductionOrder::with(['factory', 'product', 'machine', 'salesOrder.customer'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        $factories = Factory::withCount(['machines', 'productionOrders'])->get();
        $machines = Machine::with('factory')->get();

        return view('operations.index', compact(
            'pendingSalesOrders',
            'activeProductionOrders',
            'factories',
            'machines'
        ));
    }

    public function scheduleOrder(Request $request, SalesOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'operations_lead_time_days' => 'required|integer|min:1',
            'factory_id' => 'required|exists:factories,id',
            'machine_id' => 'nullable',
            'has_missing_materials' => 'nullable',
            'notes' => 'nullable|string',
        ]);

        $factory = Factory::findOrFail($validated['factory_id']);
        $leadTimeDays = (int)$validated['operations_lead_time_days'];
        $machineId = !empty($validated['machine_id']) ? (int)$validated['machine_id'] : null;

        // Compute confirmed or estimated completion date
        $startDate = Carbon::now();
        $targetEndDate = Carbon::now()->addDays($leadTimeDays);

        $items = $order->items->isNotEmpty() ? $order->items : collect([
            (object)[
                'product_id' => Product::first()?->id,
                'quantity' => 1000,
                'customization_notes' => null,
            ]
        ]);

        $hasMissingOverall = false;
        $totalMissingCount = 0;
        $allMissingItems = [];
        $createdWorkOrders = [];
        $firstWorkOrder = null;

        foreach ($items as $item) {
            $productId = $item->product_id;
            $plannedQty = (float)($item->quantity ?? 1000);
            $product = Product::with('boms.rawMaterial')->find($productId);

            $woNumber = 'WO-' . date('Y') . '-' . str_pad((ProductionOrder::count() + 1), 3, '0', STR_PAD_LEFT);

            // BOM check for this item
            $itemMissingCount = 0;
            if ($product) {
                foreach ($product->boms as $bom) {
                    $rm = $bom->rawMaterial;
                    $needed = $plannedQty * (float)$bom->quantity_per_unit;
                    $currentStock = $rm ? (float)$rm->current_stock : 0;
                    $missing = max(0, $needed - $currentStock);

                    if ($missing > 0) {
                        $itemMissingCount++;
                        if (!isset($allMissingItems[$bom->raw_material_id])) {
                            $allMissingItems[$bom->raw_material_id] = [
                                'raw_material_id' => $bom->raw_material_id,
                                'missing' => 0,
                                'unit_cost' => $rm ? (float)$rm->unit_cost : 0,
                            ];
                        }
                        $allMissingItems[$bom->raw_material_id]['missing'] += $missing;
                    }
                }
            }

            if ($itemMissingCount > 0) {
                $hasMissingOverall = true;
                $totalMissingCount += $itemMissingCount;
            }

            // Create Production Order (Work Order)
            $productionOrder = ProductionOrder::create([
                'order_number' => $woNumber,
                'sales_order_id' => $order->id,
                'factory_id' => $factory->id,
                'product_id' => $productId,
                'machine_id' => $machineId,
                'planned_quantity' => $plannedQty,
                'produced_quantity' => 0,
                'scheduled_start_date' => $startDate,
                'scheduled_end_date' => $targetEndDate,
                'estimated_hours' => max(4, round($plannedQty / 3000, 1)),
                'status' => ($itemMissingCount > 0) ? 'waiting_materials' : 'ready',
                'operations_manager_id' => Auth::id(),
                'instructions' => ($item->customization_notes ? ($item->customization_notes . ' | ') : '') . ($validated['notes'] ?? null),
            ]);

            if (!$firstWorkOrder) {
                $firstWorkOrder = $productionOrder;
            }
            $createdWorkOrders[] = $productionOrder;

            // Automatically create Material Requisition to Central Warehouse
            $mrNumber = 'MR-' . date('Y') . '-' . str_pad((MaterialRequisition::count() + 1), 3, '0', STR_PAD_LEFT);
            $materialReq = MaterialRequisition::create([
                'req_number' => $mrNumber,
                'production_order_id' => $productionOrder->id,
                'status' => 'requested',
                'requested_by' => Auth::id(),
                'requested_at' => now(),
                'notes' => 'طلب صرف مستلزمات إنتاج (' . ($product->name ?? 'صنف') . ') لأمر التشغيل ' . $woNumber . ' الخاص بطلبية ' . $order->order_number,
            ]);

            // Populate requisition items from BOM
            if ($product) {
                foreach ($product->boms as $bom) {
                    $rm = $bom->rawMaterial;
                    $needed = $plannedQty * (float)$bom->quantity_per_unit;
                    $currentStock = $rm ? (float)$rm->current_stock : 0;
                    $missing = max(0, $needed - $currentStock);

                    MaterialRequisitionItem::create([
                        'material_requisition_id' => $materialReq->id,
                        'raw_material_id' => $bom->raw_material_id,
                        'quantity_requested' => $needed,
                        'quantity_issued' => 0,
                        'quantity_missing' => $missing,
                        'unit_cost' => $rm ? $rm->unit_cost : 0,
                    ]);
                }
            }
        }

        // Respect manual selection if provided (e.g. from Dashboard or Planning form)
        $manualMissing = $request->input('has_missing_materials');
        if ($manualMissing !== null && $manualMissing !== '') {
            $hasMissingOverall = ((string)$manualMissing === '1');
        }

        if ($hasMissingOverall && empty($allMissingItems)) {
            foreach ($items as $item) {
                $p = Product::with('boms.rawMaterial')->find($item->product_id);
                if ($p) {
                    foreach ($p->boms as $b) {
                        $allMissingItems[$b->raw_material_id] = [
                            'raw_material_id' => $b->raw_material_id,
                            'missing' => (float)($item->quantity ?? 1000) * (float)$b->quantity_per_unit,
                            'unit_cost' => (float)($b->rawMaterial->unit_cost ?? 0),
                        ];
                    }
                }
            }
        }

        // If missing materials exist across any product, generate automated Purchase Requisition
        if ($hasMissingOverall) {
            $prNumber = 'PR-' . date('Y') . '-' . str_pad((PurchaseRequisition::count() + 1), 3, '0', STR_PAD_LEFT);
            $pr = PurchaseRequisition::create([
                'requisition_number' => $prNumber,
                'sales_order_id' => $order->id,
                'material_requisition_id' => $firstWorkOrder ? MaterialRequisition::where('production_order_id', $firstWorkOrder->id)->first()?->id : null,
                'factory_id' => $factory->id,
                'warehouse_manager_id' => Auth::id(),
                'status' => 'pending_procurement',
                'required_date' => $targetEndDate,
                'notes' => 'طلب شراء آلي تم توليده لوجود عجز خامات في أوامر تشغيل الطلبية ' . $order->order_number,
            ]);

            foreach ($allMissingItems as $mItem) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $pr->id,
                    'raw_material_id' => $mItem['raw_material_id'],
                    'quantity' => $mItem['missing'],
                    'unit_price_estimated' => $mItem['unit_cost'],
                    'total_price_estimated' => $mItem['missing'] * $mItem['unit_cost'],
                    'notes' => 'عجز خامات مطلوب توفيره للإنتاج',
                ]);
            }

            $order->update([
                'operations_lead_time_days' => $leadTimeDays,
                'status' => 'procurement_delayed',
                'internal_notes' => 'تم فحص خامات كافة أصناف الطلبية آلياً ورصد عجز في (' . $totalMissingCount . ') خامات. تم إرسال طلب الشراء فوراً للمشتريات (' . $prNumber . ') لتحديد مدة التوريد.',
            ]);
            $msg = 'تم فحص خامات ' . count($createdWorkOrders) . ' أصناف آلياً ورصد عجز خامات؛ تم إصدار أوامر التشغيل وتوليد طلب شراء فوري للمشتريات (' . $prNumber . ').';
        } else {
            $order->update([
                'operations_lead_time_days' => $leadTimeDays,
                'confirmed_delivery_date' => $targetEndDate,
                'status' => 'operations_scheduled',
                'internal_notes' => 'الخامات متوفرة بالكامل بالمخزن لكافة أصناف الطلبية؛ مدة التصنيع المحددة هي ' . $leadTimeDays . ' أيام عمل.',
            ]);
            $msg = 'الخامات متوفرة بالكامل لكافة أصناف الطلبية (' . count($createdWorkOrders) . ' أصناف)! تم إصدار أوامر التشغيل بنجاح (' . $leadTimeDays . ' أيام).';
        }

        return redirect()->route('operations.workOrders.show', $firstWorkOrder->id)->with('success', $msg);
    }

    public function workOrders(): View
    {
        $workOrders = ProductionOrder::with(['factory', 'product', 'machine', 'salesOrder.customer'])
            ->latest()
            ->paginate(15);
        return view('operations.work_orders.index', compact('workOrders'));
    }

    public function showWorkOrder(ProductionOrder $productionOrder): View
    {
        $productionOrder->load([
            'factory.machines',
            'product.boms.rawMaterial',
            'machine',
            'salesOrder.customer',
            'materialRequisitions.items.rawMaterial',
            'hourlyReports' => fn($q) => $q->latest()
        ]);

        return view('operations.work_orders.show', compact('productionOrder'));
    }

    public function updateWorkOrderStatus(Request $request, ProductionOrder $productionOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:ready,in_progress,completed,paused,cancelled',
            'produced_quantity' => 'nullable|numeric|min:0',
            'defective_quantity' => 'nullable|numeric|min:0',
        ]);

        $productionOrder->status = $validated['status'];

        if (isset($validated['produced_quantity'])) {
            $productionOrder->produced_quantity = $validated['produced_quantity'];
        }
        if (isset($validated['defective_quantity'])) {
            $productionOrder->defective_quantity = $validated['defective_quantity'];
        }

        if ($validated['status'] === 'in_progress' && !$productionOrder->actual_start_date) {
            $productionOrder->actual_start_date = now();
            if ($productionOrder->salesOrder) {
                $productionOrder->salesOrder->update(['status' => 'in_production']);
            }
        }

        if ($validated['status'] === 'completed') {
            $productionOrder->actual_end_date = now();
            if ($productionOrder->salesOrder) {
                $productionOrder->salesOrder->update(['status' => 'ready_in_warehouse']);
            }
        }

        $productionOrder->save();

        return back()->with('success', 'تم تحديث حالة أمر التشغيل بنجاح.');
    }

    // ================= HOURLY MACHINE REPORTS =================
    public function hourlyReports(Request $request): View
    {
        $rawDate = $request->get('date');
        $machineId = $request->get('machine_id');

        $query = HourlyMachineReport::with([
            'machine.factory',
            'factory',
            'product.boms.rawMaterial',
            'productionOrder.product.boms.rawMaterial',
            'productionOrder.salesOrder.customer'
        ]);

        // Determine selected date:
        // If explicit date is given (and not empty or 'all'):
        if ($request->has('date') && $rawDate !== 'all' && !empty($rawDate)) {
            $selectedDate = $rawDate;
            $query->whereDate('report_date', $selectedDate);
        } elseif ($request->has('date') && ($rawDate === 'all' || empty($rawDate))) {
            // User requested to see all dates!
            $selectedDate = '';
        } else {
            // First time loading page (no ?date in query):
            $today = Carbon::today()->toDateString();
            $todayExists = HourlyMachineReport::whereDate('report_date', $today)->exists();
            if ($todayExists) {
                $selectedDate = $today;
                $query->whereDate('report_date', $today);
            } else {
                $latestDate = HourlyMachineReport::max('report_date');
                $selectedDate = $latestDate ? Carbon::parse($latestDate)->toDateString() : $today;
                $query->whereDate('report_date', $selectedDate);
            }
        }

        if ($machineId) {
            $query->where('machine_id', $machineId);
        }

        // Chronological order for the day: date descending, then hour slot ascending
        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('hour_slot', 'asc')
            ->paginate(30)
            ->withQueryString();

        $machines = Machine::where('status', '!=', 'scrapped')->get();
        $factories = Factory::all();
        $products = Product::with('boms.rawMaterial')->get();
        $activeProductionOrders = ProductionOrder::with(['product.boms.rawMaterial', 'salesOrder.customer'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        // Summary calculations
        $summaryQuery = HourlyMachineReport::query();
        if ($selectedDate) {
            $summaryQuery->whereDate('report_date', $selectedDate);
        }
        if ($machineId) {
            $summaryQuery->where('machine_id', $machineId);
        }

        $totalProduced = (float)$summaryQuery->sum('units_produced');
        $avgEfficiency = (float)($summaryQuery->avg('efficiency_rate') ?? 0);
        $totalDowntime = (int)$summaryQuery->sum('downtime_minutes');
        $totalWaste = (float)$summaryQuery->sum('waste_units');

        $date = $selectedDate;

        return view('operations.hourly_reports.index', compact(
            'reports',
            'machines',
            'factories',
            'products',
            'activeProductionOrders',
            'date',
            'selectedDate',
            'machineId',
            'totalProduced',
            'avgEfficiency',
            'totalDowntime',
            'totalWaste'
        ));
    }

    public function storeHourlyReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'operator_name' => 'required|string|max:255',
            'report_date' => 'required|date',
            'hour_slot' => 'required|string|max:50',
            'units_produced' => 'required|numeric|min:0',
            'belt_speed_m_min' => 'required|numeric|min:0.01',
            'efficiency_rate' => 'nullable|numeric|min:0',
            'product_id' => 'nullable|exists:products,id',
            'production_order_id' => 'nullable|exists:production_orders,id',
            'waste_units' => 'nullable|numeric|min:0',
            'downtime_minutes' => 'nullable|integer|min:0',
            'downtime_reason' => 'nullable|string|max:255',
            'actions_taken' => 'nullable|string',
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);
        $unitsProduced = (float)$validated['units_produced'];
        $speed = (float)$validated['belt_speed_m_min']; // Machine speed in pieces per minute (قطعة / دقيقة)
        $waste = (float)($validated['waste_units'] ?? 0);
        $down = (int)($validated['downtime_minutes'] ?? 0);

        // Management Formula:
        // Theoretical Hourly Capacity = Speed (pieces/min) * 60 minutes
        // Machine Efficiency Rate (%) = (Actual Units Produced / Theoretical Hourly Capacity) * 100
        $theoreticalCapacity = $speed * 60;
        if ($theoreticalCapacity > 0) {
            $eff = round(($unitsProduced / $theoreticalCapacity) * 100, 2);
        } else {
            $eff = (float)($validated['efficiency_rate'] ?? 0);
        }

        // Generate smart decision recommendation
        $rec = HourlyMachineReport::generateRecommendation(
            $eff,
            $down,
            $waste,
            $speed,
            $machine->belt_speed_m_min
        );

        $prodId = $validated['product_id'] ?? null;
        $woId = $validated['production_order_id'] ?? null;
        if (!$prodId && $woId) {
            $wo = ProductionOrder::find($woId);
            $prodId = $wo ? $wo->product_id : null;
        }

        HourlyMachineReport::create([
            'machine_id' => $machine->id,
            'factory_id' => $machine->factory_id,
            'production_order_id' => $woId,
            'product_id' => $prodId,
            'operator_name' => $validated['operator_name'],
            'report_date' => $validated['report_date'],
            'hour_slot' => $validated['hour_slot'],
            'units_produced' => $unitsProduced,
            'belt_speed_m_min' => $speed,
            'efficiency_rate' => $eff,
            'waste_units' => $waste,
            'downtime_minutes' => $down,
            'downtime_reason' => $validated['downtime_reason'] ?? null,
            'actions_taken' => $validated['actions_taken'] ?? null,
            'ai_smart_recommendation' => $rec,
            'submitted_to_management' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('operations.hourlyReports', [
            'date' => $validated['report_date'],
            'machine_id' => $machine->id,
        ])->with('success', 'تم تسجيل تقرير الساعة (' . $validated['hour_slot'] . ') بنجاح وحساب كفاءة الماكينة أوتوماتيكياً بنسبة ' . $eff . '% وتحديث المؤشرات للإدارة العليا.');
    }

    public function updateHourlyReport(Request $request, HourlyMachineReport $hourlyMachineReport): RedirectResponse
    {
        if (!$hourlyMachineReport->is_editable) {
            return back()->with('error', 'عفواً، لا يمكن تعديل بيانات تقرير ساعة من يوم سابق (يُقفل التقرير تلقائياً بانتهاء اليوم).');
        }

        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'units_produced' => 'required|numeric|min:0',
            'belt_speed_m_min' => 'required|numeric|min:0.01',
            'waste_units' => 'nullable|numeric|min:0',
            'downtime_minutes' => 'nullable|integer|min:0',
            'downtime_reason' => 'nullable|string|max:255',
            'actions_taken' => 'nullable|string',
        ]);

        $machine = $hourlyMachineReport->machine;
        $unitsProduced = (float)$validated['units_produced'];
        $speed = (float)$validated['belt_speed_m_min'];
        $waste = (float)($validated['waste_units'] ?? 0);
        $down = (int)($validated['downtime_minutes'] ?? 0);

        $theoreticalCapacity = $speed * 60;
        $eff = $theoreticalCapacity > 0 ? round(($unitsProduced / $theoreticalCapacity) * 100, 2) : 0;

        $rec = HourlyMachineReport::generateRecommendation(
            $eff,
            $down,
            $waste,
            $speed,
            $machine->belt_speed_m_min
        );

        $hourlyMachineReport->update([
            'operator_name' => $validated['operator_name'],
            'product_id' => $validated['product_id'] ?? $hourlyMachineReport->product_id,
            'units_produced' => $unitsProduced,
            'belt_speed_m_min' => $speed,
            'efficiency_rate' => $eff,
            'waste_units' => $waste,
            'downtime_minutes' => $down,
            'downtime_reason' => $validated['downtime_reason'] ?? null,
            'actions_taken' => $validated['actions_taken'] ?? null,
            'ai_smart_recommendation' => $rec,
        ]);

        return back()->with('success', 'تم تعديل وتحديث بيانات تقرير الساعة بنجاح، وإعادة حساب الكفاءة التشغيلية (' . $eff . '%).');
    }

    // ================= MATERIAL REQUISITIONS & RECEIPT =================
    public function requisitions(): View
    {
        $requisitions = MaterialRequisition::with([
            'productionOrder.salesOrder.customer',
            'productionOrder.product.boms.rawMaterial',
            'items.rawMaterial',
            'requestedByUser',
            'issuedByUser',
            'receivedByUser'
        ])->latest()->paginate(15);

        $activeProductionOrders = ProductionOrder::with(['product.boms.rawMaterial', 'factory'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        $products = Product::where('status', true)->with('boms.rawMaterial')->get();
        $rawMaterials = RawMaterial::orderBy('name')->get();

        $productsJson = $products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'boms' => $p->boms->map(fn($b) => [
                    'raw_material_id' => $b->raw_material_id,
                    'name' => $b->rawMaterial?->name,
                    'unit' => $b->rawMaterial?->unit,
                    'stock' => (float)($b->rawMaterial?->current_stock ?? 0),
                    'qty_per_unit' => (float)$b->quantity_per_unit,
                ]),
            ];
        });

        $rawMaterialsJson = $rawMaterials->map(fn($rm) => [
            'id' => $rm->id,
            'name' => $rm->name,
            'unit' => $rm->unit,
            'stock' => (float)$rm->current_stock,
        ]);

        $pendingCount = MaterialRequisition::where('status', 'requested')->count();
        $inTransitCount = MaterialRequisition::where('status', 'issued')->count();
        $receivedCount = MaterialRequisition::where('status', 'received')->count();

        return view('operations.requisitions.index', compact(
            'requisitions',
            'activeProductionOrders',
            'products',
            'rawMaterials',
            'productsJson',
            'rawMaterialsJson',
            'pendingCount',
            'inTransitCount',
            'receivedCount'
        ));
    }

    public function storeRequisition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'nullable|exists:production_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'couvert_quantity' => 'nullable|numeric|min:1',
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|exists:raw_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string',
        ]);

        $productionOrder = null;
        if (!empty($validated['production_order_id'])) {
            $productionOrder = ProductionOrder::find($validated['production_order_id']);
        } elseif (!empty($validated['product_id'])) {
            // Started with a Couvert/Product and customized its materials!
            $product = Product::findOrFail($validated['product_id']);
            $factory = Factory::where('code', 'FAC-COUVERT')->first() ?? Factory::first();
            $couvertQty = (float)($validated['couvert_quantity'] ?? 10000);

            $woNumber = 'WO-DIR-' . date('Y') . '-' . str_pad((ProductionOrder::count() + 1), 3, '0', STR_PAD_LEFT);
            $productionOrder = ProductionOrder::create([
                'order_number' => $woNumber,
                'sales_order_id' => null,
                'factory_id' => $factory->id,
                'product_id' => $product->id,
                'planned_quantity' => $couvertQty,
                'produced_quantity' => 0,
                'status' => 'ready',
                'operations_manager_id' => Auth::id(),
                'instructions' => 'طلب خامات مخصص لإنتاج وتعديل محتويات كوڤير: ' . $product->name,
            ]);
        } else {
            $productionOrder = ProductionOrder::latest()->first();
        }

        $mrNumber = 'MR-' . date('Y') . '-' . str_pad((MaterialRequisition::count() + 1), 3, '0', STR_PAD_LEFT);
        $materialReq = MaterialRequisition::create([
            'req_number' => $mrNumber,
            'production_order_id' => $productionOrder?->id,
            'status' => 'requested',
            'requested_by' => Auth::id(),
            'requested_at' => now(),
            'notes' => $validated['notes'] ?? ('طلب صرف مستلزمات إنتاج لأمر التشغيل ' . ($productionOrder?->order_number ?? 'مباشر')),
        ]);

        foreach ($validated['items'] as $item) {
            $rm = RawMaterial::find($item['raw_material_id']);
            $needed = (float)$item['quantity'];
            $stock = $rm ? (float)$rm->current_stock : 0;
            $missing = max(0, $needed - $stock);

            MaterialRequisitionItem::create([
                'material_requisition_id' => $materialReq->id,
                'raw_material_id' => $item['raw_material_id'],
                'quantity_requested' => $needed,
                'quantity_issued' => 0,
                'quantity_missing' => $missing,
                'unit_cost' => $rm ? $rm->unit_cost : 0,
            ]);
        }

        return back()->with('success', 'تم إرسال طلب صرف الخامات (' . $mrNumber . ') إلى المخزن المركزي بنجاح وبانتظار موافقة وصرف أمين المخزن.');
    }

    public function confirmReceipt(MaterialRequisition $materialRequisition): RedirectResponse
    {
        $materialRequisition->update([
            'status' => 'received',
            'received_by' => Auth::id(),
            'received_at' => now(),
        ]);

        if ($materialRequisition->productionOrder) {
            $materialRequisition->productionOrder->update(['status' => 'ready']);
        }

        return back()->with('success', 'تم تأكيد استلام الخامات على خط الإنتاج بالمصنع بنجاح (' . $materialRequisition->req_number . ') وبدء الجاهزية للتشغيل.');
    }
}
