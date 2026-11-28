<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\TaskAssignment;
use App\Models\TaskFollowUp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $allowed = $user->isTopManagement() || $user->email === 'fadi@alphaegypt.com' || $user->isWipesSupervisor();
        if (! $allowed) {
            abort(403, 'غير مصرح: صفحة التكليفات مخصصة للإدارة العليا ومسؤولي الماكينات المكلفين فقط.');
        }

        $query = TaskAssignment::with(['assignedBy', 'assignedTo', 'machine', 'followUps.user'])
            ->latest();

        // If user is neither owner nor GM, filter strictly to tasks assigned to them
        if (! in_array($user->role, ['owner', 'gm'])) {
            $query->where('assigned_to_user_id', $user->id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to_user_id', $request->assigned_to);
        }

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->paginate(15)->withQueryString();
        $employees = User::where('status', true)->orderBy('name')->get();
        $machines = $user->isWipesSupervisor()
            ? Machine::where('code', 'MCH-WIPES-01')->get()
            : Machine::orderBy('name')->get();

        // Metrics scoped to user's visibility
        $metricsBase = TaskAssignment::query();
        if (! in_array($user->role, ['owner', 'gm'])) {
            $metricsBase->where('assigned_to_user_id', $user->id);
        }
        $totalTasksCount = (clone $metricsBase)->count();
        $overtimeCount = (clone $metricsBase)->where('task_type', 'overtime')->count();
        $inProgressCount = (clone $metricsBase)->where('status', 'in_progress')->count();
        $completedCount = (clone $metricsBase)->where('status', 'completed')->count();

        return view('tasks.index', compact(
            'tasks',
            'employees',
            'machines',
            'totalTasksCount',
            'overtimeCount',
            'inProgressCount',
            'completedCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! auth()->user()->isTopManagement()) {
            abort(403, 'غير مصرح: صلاحية إضافة وتوزيع المهام مقتصرة على الإدارة العليا والمدير العام فقط.');
        }

        $validated = $request->validate([
            'assigned_to_user_id' => 'required|exists:users,id',
            'machine_id' => 'nullable|exists:machines,id',
            'title' => 'required|string|max:255',
            'task_type' => 'required|string|in:overtime,maintenance,supervision,operational,general',
            'priority' => 'required|string|in:urgent,high,normal,low',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $task = TaskAssignment::create([
            'assigned_by_user_id' => auth()->id() ?? 1,
            'assigned_to_user_id' => $validated['assigned_to_user_id'],
            'machine_id' => $validated['machine_id'] ?: null,
            'title' => $validated['title'],
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'in_progress',
            'description' => $validated['description'],
            'due_date' => $validated['due_date'] ?: null,
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', 'تم تسجيل التكليف والعمل الإضافي بنجاح وتعيينه للمسؤول.');
    }

    public function update(Request $request, TaskAssignment $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,paused',
            'priority' => 'nullable|string|in:urgent,high,normal,low',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'status' => $validated['status'],
        ];

        if (! empty($validated['priority'])) {
            $data['priority'] = $validated['priority'];
        }

        if (isset($validated['notes'])) {
            $data['notes'] = $validated['notes'];
        }

        if ($validated['status'] === 'completed' && ! $task->completed_at) {
            $data['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return back()->with('success', 'تم تحديث حالة التكليف بنجاح.');
    }

    public function storeFollowUp(Request $request, TaskAssignment $task): RedirectResponse
    {
        $validated = $request->validate([
            'report_date' => 'required|date',
            'notes' => 'required|string',
            'downtime_minutes' => 'nullable|integer|min:0',
            'spare_parts_used' => 'nullable|string|max:255',
            'machine_status' => 'required|string|in:running,stopped,maintenance',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        TaskFollowUp::create([
            'task_assignment_id' => $task->id,
            'user_id' => auth()->id() ?? $task->assigned_to_user_id,
            'report_date' => $validated['report_date'],
            'notes' => $validated['notes'],
            'downtime_minutes' => $validated['downtime_minutes'] ?? 0,
            'spare_parts_used' => $validated['spare_parts_used'] ?? null,
            'machine_status' => $validated['machine_status'],
            'progress_percentage' => $validated['progress_percentage'] ?? 100,
        ]);

        if ($task->machine_id && $validated['machine_status']) {
            $task->machine->update(['status' => $validated['machine_status']]);
        }

        return back()->with('success', 'تم تسجيل تقرير المتابعة والإنجاز الدوري للتكليف بنجاح.');
    }
}
