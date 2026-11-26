<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\RawMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $machines = Machine::with(['factory', 'hourlyReports' => fn ($q) => $q->latest()->take(5)])->get();
        $spareParts = RawMaterial::where('category', 'spare_part')->get();
        $recentDowntime = HourlyMachineReport::with(['machine.factory'])
            ->where('downtime_minutes', '>', 0)
            ->latest()
            ->take(10)
            ->get();

        $factories = Factory::all();

        return view('maintenance.index', compact('machines', 'spareParts', 'recentDowntime', 'factories'));
    }

    public function updateMachineStatus(Request $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:running,idle,maintenance,breakdown',
            'maintenance_due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $machine->update($validated);

        return back()->with('success', 'تم تحديث الحالة الفنية للماكينة بنجاح.');
    }

    public function storeMachine(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'factory_id' => 'required|exists:factories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:machines,code',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'rated_speed_units_per_hour' => 'required|numeric|min:1',
            'belt_speed_m_min' => 'required|numeric|min:0.1',
            'maintenance_due_date' => 'nullable|date',
        ]);

        Machine::create(array_merge($validated, ['status' => 'running']));

        return back()->with('success', 'تم تسجيل الماكينة الجديدة وإضافتها لخط الإنتاج بنجاح.');
    }
}
