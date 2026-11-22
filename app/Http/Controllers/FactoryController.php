<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactoryController extends Controller
{
    public function index(): View
    {
        $factories = Factory::with(['machines', 'productionOrders'])->get();
        $today = Carbon::today()->toDateString();

        $factoryStats = [];
        foreach ($factories as $f) {
            // Internal revenue from central warehouse purchases + 14% markup
            $internalRevenue = (float)PurchaseOrder::where('from_factory_id', $f->id)
                ->where('status', 'received_in_warehouse')
                ->sum('total_amount');

            // 14% markup amount earned
            $markup14Earned = (float)PurchaseOrder::where('from_factory_id', $f->id)
                ->where('status', 'received_in_warehouse')
                ->sum('internal_markup_amount');

            // Recorded costs
            $costs = (float)FinancialTransaction::where('factory_id', $f->id)
                ->whereIn('type', ['variable_cost', 'fixed_cost', 'maintenance'])
                ->sum('amount');

            // Hourly units produced today
            $unitsToday = (float)HourlyMachineReport::where('factory_id', $f->id)
                ->whereDate('report_date', $today)
                ->sum('units_produced');

            $avgEff = (float)HourlyMachineReport::where('factory_id', $f->id)
                ->whereDate('report_date', $today)
                ->avg('efficiency_rate') ?? 95.0;

            $downtimeToday = (int)HourlyMachineReport::where('factory_id', $f->id)
                ->whereDate('report_date', $today)
                ->sum('downtime_minutes');

            $activeOrdersCount = ProductionOrder::where('factory_id', $f->id)
                ->whereIn('status', ['ready', 'in_progress'])
                ->count();

            $factoryStats[$f->id] = [
                'internalRevenue' => $internalRevenue,
                'markup14Earned' => $markup14Earned,
                'costs' => $costs,
                'netProfit' => ($internalRevenue - $costs),
                'unitsToday' => $unitsToday,
                'avgEff' => $avgEff,
                'downtimeToday' => $downtimeToday,
                'activeOrdersCount' => $activeOrdersCount,
            ];
        }

        return view('factories.index', compact('factories', 'factoryStats'));
    }

    public function show(Factory $factory): View
    {
        $factory->load([
            'machines.hourlyReports' => fn($q) => $q->latest()->take(10),
            'productionOrders.product',
            'hourlyReports' => fn($q) => $q->latest()->take(20),
            'supervisor'
        ]);

        $today = Carbon::today()->toDateString();

        // 14% internal sales to central warehouse
        $internalPurchaseOrders = PurchaseOrder::where('from_factory_id', $factory->id)
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        $totalInternalRevenue = PurchaseOrder::where('from_factory_id', $factory->id)
            ->where('status', 'received_in_warehouse')
            ->sum('total_amount');

        $totalMarkup14 = PurchaseOrder::where('from_factory_id', $factory->id)
            ->where('status', 'received_in_warehouse')
            ->sum('internal_markup_amount');

        $totalCosts = FinancialTransaction::where('factory_id', $factory->id)->sum('amount');
        $netProfit = $totalInternalRevenue - $totalCosts;

        $recentHourlyReports = HourlyMachineReport::where('factory_id', $factory->id)
            ->with('machine')
            ->latest()
            ->take(15)
            ->get();

        return view('factories.show', compact(
            'factory',
            'internalPurchaseOrders',
            'totalInternalRevenue',
            'totalMarkup14',
            'totalCosts',
            'netProfit',
            'recentHourlyReports'
        ));
    }
}
