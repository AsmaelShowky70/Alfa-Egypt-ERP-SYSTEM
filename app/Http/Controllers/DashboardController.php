<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyInventoryCheck;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\MaterialRequisition;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 1. Common KPIs and Alarm Counters
        $lossAlarmsCount = SalesOrder::where('has_loss_alarm', true)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $lowStockMaterialsCount = RawMaterial::whereColumn('current_stock', '<=', 'reorder_level')->count();
        $pendingOperationsCount = SalesOrder::where('status', 'pending_operations')->count();
        $pendingPurchasingRequisitionsCount = PurchaseRequisition::where('status', 'pending_procurement')->count();

        // 2. Data payload tailored specifically to the user's role
        $data = [
            'user' => $user,
            'lossAlarmsCount' => $lossAlarmsCount,
            'lowStockMaterialsCount' => $lowStockMaterialsCount,
            'pendingOperationsCount' => $pendingOperationsCount,
            'pendingPurchasingRequisitionsCount' => $pendingPurchasingRequisitionsCount,
        ];

        if ($user->isOwner() || $user->isGM()) {
            // Executive Top Management Dashboard
            $totalSalesRevenue = FinancialTransaction::where('type', 'customer_payment')->sum('amount');
            $totalExpenses = FinancialTransaction::whereIn('type', ['fixed_cost', 'variable_cost', 'supplier_payment'])->sum('amount');
            $totalFactoryMarkup14 = FinancialTransaction::where('type', 'factory_internal_14_markup')->sum('amount');
            
            $warehouseRawValue = RawMaterial::sum(\DB::raw('current_stock * unit_cost'));
            $warehouseFinishedValue = Product::sum(\DB::raw('current_stock * cost_price'));
            $totalWarehouseValue = $warehouseRawValue + $warehouseFinishedValue;

            $factories = Factory::withCount(['machines', 'productionOrders'])->get();
            $recentOrders = SalesOrder::with(['customer', 'items.product'])->latest()->take(6)->get();
            $hourlyReportsToday = HourlyMachineReport::with(['machine', 'factory'])
                ->whereDate('report_date', $today)
                ->latest()
                ->take(8)
                ->get();

            // Factory P&L calculations
            $factoryPerformance = [];
            foreach ($factories as $f) {
                $rev = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['customer_payment', 'factory_internal_14_markup'])->sum('amount');
                $cost = FinancialTransaction::where('factory_id', $f->id)->whereIn('type', ['variable_cost', 'fixed_cost'])->sum('amount');
                $factoryPerformance[] = [
                    'name' => $f->name,
                    'code' => $f->code,
                    'type' => $f->type,
                    'revenue' => (float)$rev,
                    'cost' => (float)$cost,
                    'profit' => (float)($rev - $cost),
                ];
            }

            $lossOrders = SalesOrder::with('customer')
                ->where('has_loss_alarm', true)
                ->latest()
                ->take(5)
                ->get();

            $data = array_merge($data, compact(
                'totalSalesRevenue',
                'totalExpenses',
                'totalFactoryMarkup14',
                'totalWarehouseValue',
                'factories',
                'recentOrders',
                'hourlyReportsToday',
                'factoryPerformance',
                'lossOrders'
            ));

            return view('dashboard.executive', $data);
        }

        if ($user->isSales()) {
            // Sales Department Dashboard
            $totalCustomers = Customer::where('status', true)->count();
            $totalOrdersCount = SalesOrder::count();
            $activeOrdersCount = SalesOrder::whereNotIn('status', ['completed', 'cancelled'])->count();
            $totalSalesValue = SalesOrder::sum('total_amount');

            $recentOrders = SalesOrder::with(['customer', 'items.product'])
                ->latest()
                ->take(10)
                ->get();

            $topCustomers = Customer::withCount('salesOrders')
                ->orderByDesc('sales_orders_count')
                ->take(6)
                ->get();

            $lossOrders = SalesOrder::with('customer')
                ->where('has_loss_alarm', true)
                ->latest()
                ->get();

            $data = array_merge($data, compact(
                'totalCustomers',
                'totalOrdersCount',
                'activeOrdersCount',
                'totalSalesValue',
                'recentOrders',
                'topCustomers',
                'lossOrders'
            ));

            return view('dashboard.sales', $data);
        }

        if ($user->isOperations()) {
            // Operations & Production Dashboard
            $activeWorkOrders = ProductionOrder::with(['factory', 'product', 'machine'])
                ->whereIn('status', ['ready', 'in_progress', 'waiting_materials'])
                ->latest()
                ->get();

            $todayProducedUnits = HourlyMachineReport::whereDate('report_date', $today)->sum('units_produced');
            $avgEfficiencyToday = HourlyMachineReport::whereDate('report_date', $today)->avg('efficiency_rate') ?? 95.0;
            $todayDowntimeMinutes = HourlyMachineReport::whereDate('report_date', $today)->sum('downtime_minutes');

            $machines = Machine::with('factory')->get();
            $pendingOrdersForScheduling = SalesOrder::with(['customer', 'items.product'])
                ->whereIn('status', ['pending_operations', 'procurement_delayed'])
                ->latest()
                ->get();

            $hourlyReportsToday = HourlyMachineReport::with(['machine', 'factory'])
                ->whereDate('report_date', $today)
                ->latest()
                ->take(10)
                ->get();

            $data = array_merge($data, compact(
                'activeWorkOrders',
                'todayProducedUnits',
                'avgEfficiencyToday',
                'todayDowntimeMinutes',
                'machines',
                'pendingOrdersForScheduling',
                'hourlyReportsToday'
            ));

            return view('dashboard.operations', $data);
        }

        if ($user->isWarehouse()) {
            // Warehouse Department Dashboard
            $rawMaterialsCount = RawMaterial::count();
            $finishedGoodsCount = Product::count();
            $sparePartsCount = RawMaterial::where('category', 'spare_part')->count();

            $rawMaterialsValue = (float)RawMaterial::where('category', '!=', 'spare_part')->get()->sum(fn($r) => (float)$r->current_stock * (float)$r->unit_cost);
            $finishedGoodsValue = (float)Product::get()->sum(fn($p) => (float)$p->current_stock * (float)$p->cost_price);
            $sparePartsValue = (float)RawMaterial::where('category', 'spare_part')->get()->sum(fn($r) => (float)$r->current_stock * (float)$r->unit_cost);

            $lowStockMaterials = RawMaterial::whereColumn('current_stock', '<=', 'reorder_level')
                ->with('supplier')
                ->get();
            $lowStockItems = $lowStockMaterials;

            $recentMovements = StockMovement::with(['items', 'warehouseManager'])
                ->latest()
                ->take(10)
                ->get();
            $recentVouchers = $recentMovements;

            $pendingRequisitions = MaterialRequisition::with(['productionOrder.product', 'items.rawMaterial'])
                ->where('status', 'requested')
                ->latest()
                ->get();
            $pendingMaterialRequisitions = $pendingRequisitions;

            $latestDailyCheck = DailyInventoryCheck::with('items')->latest()->first();

            $data = array_merge($data, compact(
                'rawMaterialsCount',
                'finishedGoodsCount',
                'sparePartsCount',
                'rawMaterialsValue',
                'finishedGoodsValue',
                'sparePartsValue',
                'lowStockItems',
                'lowStockMaterials',
                'recentMovements',
                'recentVouchers',
                'pendingRequisitions',
                'pendingMaterialRequisitions',
                'latestDailyCheck'
            ));

            return view('dashboard.warehouse', $data);
        }

        if ($user->isPurchasing()) {
            // Purchasing Department Dashboard
            $pendingRequisitions = PurchaseRequisition::with(['items.rawMaterial', 'salesOrder.customer', 'warehouseManager'])
                ->whereIn('status', ['pending_procurement', 'lead_time_quoted'])
                ->latest()
                ->get();

            $activePurchaseOrders = PurchaseOrder::with(['supplier', 'fromFactory', 'items'])
                ->whereNotIn('status', ['received_in_warehouse', 'cancelled'])
                ->latest()
                ->get();

            $suppliers = Supplier::where('status', true)->withCount('purchaseOrders')->get();
            $totalSpend = PurchaseOrder::where('status', 'received_in_warehouse')->sum('total_amount');

            $data = array_merge($data, compact(
                'pendingRequisitions',
                'activePurchaseOrders',
                'suppliers',
                'totalSpend'
            ));

            return view('dashboard.purchasing', $data);
        }

        if ($user->isFinance()) {
            // Finance & Accounts Dashboard
            $totalRevenue = FinancialTransaction::where('type', 'customer_payment')->sum('amount');
            $fixedCosts = FinancialTransaction::where('type', 'fixed_cost')->sum('amount');
            $variableCosts = FinancialTransaction::where('type', 'variable_cost')->sum('amount');
            $markup14Total = FinancialTransaction::where('type', 'factory_internal_14_markup')->sum('amount');

            $recentTransactions = FinancialTransaction::with(['salesOrder', 'purchaseOrder', 'factory'])
                ->latest()
                ->take(10)
                ->get();

            $productsWithCosts = Product::with('customer')->get();

            $lossAlertOrders = SalesOrder::with('customer')
                ->where('has_loss_alarm', true)
                ->latest()
                ->get();

            $data = array_merge($data, compact(
                'totalRevenue',
                'fixedCosts',
                'variableCosts',
                'markup14Total',
                'recentTransactions',
                'productsWithCosts',
                'lossAlertOrders'
            ));

            return view('dashboard.finance', $data);
        }

        if ($user->isMaintenance()) {
            // Maintenance Department Dashboard
            $machines = Machine::with(['factory', 'hourlyReports'])->get();
            $totalMachines = $machines->count();
            $runningMachines = $machines->where('status', 'running')->count();
            $maintenanceDueMachines = $machines->where('status', 'maintenance')->count();

            $spareParts = RawMaterial::where('category', 'spare_part')->get();
            $lowSpareParts = $spareParts->filter(fn($p) => $p->current_stock <= $p->reorder_level);

            $recentDowntimeReports = HourlyMachineReport::with(['machine', 'factory'])
                ->where('downtime_minutes', '>', 0)
                ->latest()
                ->take(8)
                ->get();

            $data = array_merge($data, compact(
                'machines',
                'totalMachines',
                'runningMachines',
                'maintenanceDueMachines',
                'spareParts',
                'lowSpareParts',
                'recentDowntimeReports'
            ));

            return view('dashboard.maintenance', $data);
        }

        return view('dashboard.index', $data);
    }
}
