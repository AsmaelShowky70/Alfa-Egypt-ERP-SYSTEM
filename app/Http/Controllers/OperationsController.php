<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionItem;
use App\Models\Product;
use App\Models\ProductionOrder;
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
            'machine_id' => 'nullable|exists:machines,id',
            'has_missing_materials' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $factory = Factory::findOrFail($validated['factory_id']);
        $leadTimeDays = (int)$validated['operations_lead_time_days'];
        $hasMissing = (bool)$validated['has_missing_materials'];

        // Compute confirmed or estimated completion date
        $startDate = Carbon::now();
        $targetEndDate = Carbon::now()->addDays($leadTimeDays);

        // Create Production Order (Work Order)
        $firstItem = $order->items->first();
        $productId = $firstItem ? $firstItem->product_id : Product::first()->id;
        $plannedQty = $firstItem ? $firstItem->quantity : 1000;

        $woNumber = 'WO-' . date('Y') . '-' . str_pad((ProductionOrder::count() + 1), 3, '0', STR_PAD_LEFT);

        $productionOrder = ProductionOrder::create([
            'order_number' => $woNumber,
            'sales_order_id' => $order->id,
            'factory_id' => $factory->id,
            'product_id' => $productId,
            'machine_id' => $validated['machine_id'],
            'planned_quantity' => $plannedQty,
            'produced_quantity' => 0,
            'scheduled_start_date' => $startDate,
            'scheduled_end_date' => $targetEndDate,
            'estimated_hours' => $leadTimeDays * 8, // 8 hours per day
            'status' => $hasMissing ? 'waiting_materials' : 'ready',
            'operations_manager_id' => Auth::id(),
            'instructions' => $validated['notes'],
        ]);

        // Automatically create Material Requisition to Central Warehouse
        $mrNumber = 'MR-' . date('Y') . '-' . str_pad((MaterialRequisition::count() + 1), 3, '0', STR_PAD_LEFT);
        $materialReq = MaterialRequisition::create([
            'req_number' => $mrNumber,
            'production_order_id' => $productionOrder->id,
            'status' => 'requested',
            'requested_by' => Auth::id(),
            'requested_at' => now(),
            'notes' => 'طلب صرف مستلزمات الإنتاج لأمر التشغيل ' . $woNumber . ' الخاص بطلبية ' . $order->order_number,
        ]);

        // Populate required materials from BOM
        $product = Product::with('boms.rawMaterial')->find($productId);
        $missingCount = 0;

        if ($product) {
            foreach ($product->boms as $bom) {
                $rm = $bom->rawMaterial;
                $needed = $plannedQty * $bom->quantity_per_unit;
                $currentStock = $rm ? (float)$rm->current_stock : 0;
                $missing = max(0, $needed - $currentStock);

                if ($missing > 0) {
                    $missingCount++;
                }

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

        if ($hasMissing || $missingCount > 0) {
            $order->update([
                'operations_lead_time_days' => $leadTimeDays,
                'status' => 'procurement_delayed', // Flag to sales that procurement lead time must be checked!
                'internal_notes' => 'أفاد مدير التشغيل بأن مدة التصنيع هي ' . $leadTimeDays . ' أيام ولكن هناك خامات ناقصة يلزم توفيرها من المشتريات أولاً.',
            ]);
            $msg = 'تم تسجيل أمر التشغيل وطلب صرف المستلزمات؛ نظراً لنقص بعض الخامات تم تحويل الطلب لحالة (بانتظار رد المشتريات).';
        } else {
            $order->update([
                'operations_lead_time_days' => $leadTimeDays,
                'confirmed_delivery_date' => $targetEndDate,
                'status' => 'operations_scheduled',
                'internal_notes' => 'الخامات متوفرة وتم حجزها؛ مدة التصنيع المحددة من التشغيل هي ' . $leadTimeDays . ' أيام عمل.',
            ]);
            $msg = 'تم فحص الخامات وتأكيد توفرها وتحديد مدة التصنيع (' . $leadTimeDays . ' أيام) بنجاح. أصبحت المبيعات قادرة على تأكيد الميعاد مع العميل.';
        }

        return redirect()->route('operations.workOrders.show', $productionOrder->id)->with('success', $msg);
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
        $date = $request->get('date', Carbon::today()->toDateString());
        $machineId = $request->get('machine_id');

        $query = HourlyMachineReport::with(['machine.factory', 'factory'])
            ->whereDate('report_date', $date);

        if ($machineId) {
            $query->where('machine_id', $machineId);
        }

        $reports = $query->latest()->paginate(20);
        $machines = Machine::where('status', '!=', 'scrapped')->get();
        $factories = Factory::all();

        // Summary calculations
        $totalProduced = (float)HourlyMachineReport::whereDate('report_date', $date)->sum('units_produced');
        $avgEfficiency = (float)HourlyMachineReport::whereDate('report_date', $date)->avg('efficiency_rate') ?? 0;
        $totalDowntime = (int)HourlyMachineReport::whereDate('report_date', $date)->sum('downtime_minutes');
        $totalWaste = (float)HourlyMachineReport::whereDate('report_date', $date)->sum('waste_units');

        return view('operations.hourly_reports.index', compact(
            'reports',
            'machines',
            'factories',
            'date',
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
            'waste_units' => 'nullable|numeric|min:0',
            'downtime_minutes' => 'nullable|integer|min:0',
            'downtime_reason' => 'nullable|string|max:255',
            'actions_taken' => 'nullable|string',
            'production_order_id' => 'nullable|exists:production_orders,id',
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

        HourlyMachineReport::create([
            'machine_id' => $machine->id,
            'factory_id' => $machine->factory_id,
            'production_order_id' => $validated['production_order_id'] ?? null,
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

        return back()->with('success', 'تم حساب كفاءة الماكينة أوتوماتيكياً (' . $eff . '%) وتسجيل تقرير الساعة ورفعه للإدارة العليا بنجاح.');
    }
}
