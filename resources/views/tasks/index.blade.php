@extends('layouts.app')

@section('title', 'المهام والتكليفات الإضافية ومتابعة الماكينات')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-purple-100 text-purple-700 rounded-xl">
                    <i class="fa-solid fa-list-check text-xl"></i>
                </span>
                <h1 class="text-2xl font-black text-slate-900">سجل المهام والتكليفات الإضافية ومتابعة الماكينات</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">تكليفات الإدارة العليا للمهندسين والمشرفين، مهام العمل الإضافي، ومتابعة ماكينات الجوانتي والوايبس وخطوط الإنتاج</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isTopManagement())
                <a href="{{ route('users.create') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-user-plus text-slate-500"></i>
                    <span>إضافة موظف / مهندس جديد</span>
                </a>
                <button type="button" onclick="openNewTaskModal()" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>تكليف عمل إضافي / مهمة جديدة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-bold">إجمالي التكليفات</span>
                <span class="text-xl font-black text-slate-800">{{ $totalTasksCount }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-business-time"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-bold">تكليفات عمل إضافي</span>
                <span class="text-xl font-black text-amber-700">{{ $overtimeCount }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-bold">قيد التنفيذ والمتابعة</span>
                <span class="text-xl font-black text-emerald-700">{{ $inProgressCount }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-bold">مهام مكتملة</span>
                <span class="text-xl font-black text-blue-700">{{ $completedCount }}</span>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">المسؤول المكلف</label>
                <select name="assigned_to" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    <option value="">جميع الموظفين والمهندسين</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->job_title }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">الماكينة / الخط المرتبط</label>
                <select name="machine_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    <option value="">جميع الماكينات والخطوط</option>
                    @foreach($machines as $mch)
                        <option value="{{ $mch->id }}" {{ request('machine_id') == $mch->id ? 'selected' : '' }}>
                            {{ $mch->name }} ({{ $mch->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">نوع التكليف</label>
                <select name="task_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    <option value="">جميع الأنواع</option>
                    <option value="overtime" {{ request('task_type') == 'overtime' ? 'selected' : '' }}>عمل إضافي (Overtime)</option>
                    <option value="maintenance" {{ request('task_type') == 'maintenance' ? 'selected' : '' }}>صيانة دورية ووقائية</option>
                    <option value="supervision" {{ request('task_type') == 'supervision' ? 'selected' : '' }}>إشراف وتشغيل</option>
                    <option value="operational" {{ request('task_type') == 'operational' ? 'selected' : '' }}>مهمة تشغيلية</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">حالة التكليف</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    <option value="">جميع الحالات</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ والمتابعة</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل ومعتمد</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>بانتظار البدء</option>
                    <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>متوقف مؤقتاً</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>تطبيق الفلترة</span>
                </button>
                <a href="{{ route('tasks.index') }}" class="px-3 py-2 border border-slate-200 hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-bold transition">
                    إعادة ضبط
                </a>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="space-y-4">
        @forelse($tasks as $task)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-5">
                <!-- Task Header -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-extrabold px-3 py-1 rounded-full border {{ $task->task_type_badge_class }}">
                                {{ $task->task_type_arabic_name }}
                            </span>
                            <span class="text-[11px] font-extrabold px-3 py-1 rounded-full border {{ $task->priority_badge_class }}">
                                {{ $task->priority_arabic_name }}
                            </span>
                            <span class="text-[11px] font-extrabold px-3 py-1 rounded-full border {{ $task->status_badge_class }}">
                                {{ $task->status_arabic_name }}
                            </span>
                        </div>
                        <h2 class="text-lg font-black text-slate-900 mt-2">{{ $task->title }}</h2>
                    </div>

                    <!-- Machine Badge if linked -->
                    @if($task->machine)
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-2xl">
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-gears"></i>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-bold">الماكينة المرتبطة</span>
                                <span class="text-xs font-black text-slate-800">{{ $task->machine->name }}</span>
                                <span class="text-[10px] font-mono text-purple-600 font-bold block">{{ $task->machine->code }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Assignment Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <!-- Assigned Person -->
                    <div class="flex items-center gap-3 bg-purple-50/50 p-3 rounded-2xl border border-purple-100/60">
                        @if($task->assignedTo->avatar)
                            <img src="{{ asset($task->assignedTo->avatar) }}" class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-sm" alt="{{ $task->assignedTo->name }}">
                        @else
                            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-black text-base shadow-sm">
                                {{ mb_substr($task->assignedTo->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <span class="text-[10px] text-purple-600 font-bold block">المسؤول المكلف</span>
                            <span class="font-extrabold text-slate-900 block text-sm">{{ $task->assignedTo->name }}</span>
                            <span class="text-[11px] text-slate-500 font-medium">
                                @if($task->task_type === 'overtime' && str_contains($task->title, 'متابعة إنتاجية'))
                                    متابعة إنتاجية مكنتي الجوانتي (عمل إضافي من التشغيل)
                                @else
                                    {{ $task->assignedTo->job_title }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Assigned By & Due Date -->
                    <div class="space-y-2 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">صادر من:</span>
                            <span class="font-bold text-slate-800">{{ $task->assignedBy->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px]">تاريخ التكليف:</span>
                            <span class="font-semibold text-slate-700">{{ $task->created_at->format('Y-m-d') }}</span>
                        </div>
                        @if($task->due_date)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">تاريخ الاستحقاق:</span>
                                <span class="font-bold text-amber-700">{{ $task->due_date->format('Y-m-d') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Instructions / Description -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-between">
                        <span class="text-[10px] text-slate-400 font-bold block mb-1">تعليمات وتفاصيل التكليف:</span>
                        <p class="text-slate-700 leading-relaxed text-[11px] font-medium">{{ $task->description ?? 'لا توجد تعليمات إضافية مسجلة.' }}</p>
                    </div>
                </div>

                <!-- Follow-up Reports Section -->
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
                            <span>تقارير المتابعة والإنجاز الدوري ({{ $task->followUps->count() }})</span>
                        </h4>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    onclick='openFollowUpModal({{ $task->id }}, "{{ addslashes($task->title) }}", "{{ $task->machine?->name ?? '' }}")'
                                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-plus-circle text-xs"></i>
                                <span>تسجيل تقرير متابعة</span>
                            </button>

                            <button type="button" 
                                    onclick='openUpdateStatusModal({{ $task->id }}, "{{ $task->status }}", "{{ $task->priority }}", "{{ addslashes($task->notes ?? '') }}")'
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                <i class="fa-solid fa-pen text-slate-400"></i>
                                <span>تحديث الحالة</span>
                            </button>
                        </div>
                    </div>

                    <!-- Reports list -->
                    @if($task->followUps->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($task->followUps as $fu)
                                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs space-y-2">
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ $fu->user->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $fu->report_date->format('Y-m-d') }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $fu->machine_status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                {{ $fu->machine_status === 'running' ? 'الماكينة تعمل' : ($fu->machine_status === 'maintenance' ? 'تحت الصيانة' : 'متوقفة') }}
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                                إنجاز {{ $fu->progress_percentage }}%
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-slate-700 leading-relaxed font-medium">{{ $fu->notes }}</p>
                                    @if($fu->spare_parts_used)
                                        <div class="text-[11px] text-purple-700 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-wrench text-[10px]"></i>
                                            <span>قطع الغيار والمستهلكات: {{ $fu->spare_parts_used }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-4 text-center text-xs text-slate-400">
                            لا توجد تقارير متابعة مسجلة حتى الآن. انقر على "تسجيل تقرير متابعة" لإضافة تقرير متابعة إنتاجية أو تشغيل.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm space-y-3">
                <div class="w-16 h-16 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto font-bold">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h3 class="text-base font-black text-slate-800">لا توجد تكليفات مطابقة لمعايير البحث</h3>
                <p class="text-xs text-slate-500">يمكن للإدارة العليا إصدار تكليف جديد أو عمل إضافي للمهندسين والمشرفين.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $tasks->links() }}
        </div>
    </div>

    <!-- Modal: Add New Task Assignment -->
    <div id="newTaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-purple-600"></i>
                    <span>تكليف عمل إضافي أو مهمة جديدة</span>
                </h3>
                <button type="button" onclick="closeNewTaskModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">عنوان التكليف أو العمل الإضافي *</label>
                    <input type="text" name="title" required placeholder="مثال: عمل إضافي: متابعة إنتاجية ماكينة تقطيع الجوانتيات" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الموظف / المهندس المسؤول *</label>
                        <select name="assigned_to_user_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="">اختر المسؤول المكلف...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }} — {{ $emp->job_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الماكينة أو الخط المرتبط</label>
                        <select name="machine_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="">لا يوجد (مهمة عامة / إدارية)</option>
                            @foreach($machines as $mch)
                                <option value="{{ $mch->id }}">
                                    {{ $mch->name }} ({{ $mch->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">نوع التكليف *</label>
                        <select name="task_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="overtime">عمل إضافي (Overtime)</option>
                            <option value="maintenance">صيانة دورية ووقائية</option>
                            <option value="supervision">إشراف وتشغيل</option>
                            <option value="operational">مهمة تشغيلية</option>
                            <option value="general">تكليف إداري عام</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">درجة الأولوية *</label>
                        <select name="priority" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="urgent">عاجل وحرج</option>
                            <option value="high" selected>مرتفعة</option>
                            <option value="normal">متوسطة</option>
                            <option value="low">عادية</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(7)->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تعليمات وتفاصيل العمل المطلوب</label>
                    <textarea name="description" rows="3" placeholder="اكتب تفاصيل التكليف، المعايير الفنية، وجدول المتابعة المطلوب..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeNewTaskModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">اعتماد وحفظ التكليف</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Add Follow-Up Report -->
    <div id="followUpModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-waveform text-emerald-600"></i>
                    <span>تسجيل تقرير متابعة إنتاجية وتشغيل</span>
                </h3>
                <button type="button" onclick="closeFollowUpModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="followUpForm" method="POST" class="space-y-4">
                @csrf
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs text-slate-700 space-y-1">
                    <div>التكليف: <b id="followUpTaskTitle" class="text-purple-700"></b></div>
                    <div id="followUpMachineContainer">الماكينة: <b id="followUpMachineName" class="text-slate-900"></b></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ التقرير *</label>
                        <input type="date" name="report_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">حالة الماكينة الحالية *</label>
                        <select name="machine_status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-emerald-600 outline-none">
                            <option value="running">الماكينة تعمل بكفاءة (Running)</option>
                            <option value="maintenance">تحت الصيانة والإصلاح (Maintenance)</option>
                            <option value="stopped">متوقفة مؤقتاً (Stopped)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">دقائق التوقف (إن وجدت)</label>
                        <input type="number" name="downtime_minutes" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">نسبة الإنجاز % *</label>
                        <input type="number" name="progress_percentage" value="100" min="0" max="100" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات وتقرير المتابعة الإنتاجية والتشغيل *</label>
                    <textarea name="notes" rows="3" required placeholder="اكتب كميات الإنتاج، نسبة الكفاءة، الهدر، وحالة الماكينة..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">قطع الغيار والمستهلكات المستخدمة (إن وجدت)</label>
                    <input type="text" name="spare_parts_used" placeholder="مثال: شحم حراري، رولمان بلي، شريط تيفلون، سكاكين..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeFollowUpModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow">حفظ تقرير المتابعة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Update Task Status -->
    <div id="updateStatusModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-purple-600"></i>
                    <span>تحديث حالة التكليف</span>
                </h3>
                <button type="button" onclick="closeUpdateStatusModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="updateStatusForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">حالة التكليف *</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none">
                        <option value="in_progress">قيد التنفيذ والمتابعة</option>
                        <option value="completed">مكتمل ومعتمد</option>
                        <option value="pending">بانتظار البدء</option>
                        <option value="paused">متوقف مؤقتاً</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الأولوية</label>
                    <select id="edit_priority" name="priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none">
                        <option value="urgent">عاجل وحرج</option>
                        <option value="high">مرتفعة</option>
                        <option value="normal">متوسطة</option>
                        <option value="low">عادية</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات إضافية</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeUpdateStatusModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">تحديث الحالة</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openNewTaskModal() {
    const modal = document.getElementById('newTaskModal');
    if (modal) modal.classList.remove('hidden');
}

function closeNewTaskModal() {
    const modal = document.getElementById('newTaskModal');
    if (modal) modal.classList.add('hidden');
}

function openFollowUpModal(taskId, taskTitle, machineName) {
    const modal = document.getElementById('followUpModal');
    const form = document.getElementById('followUpForm');
    if (!modal || !form) return;

    form.action = '/tasks/' + taskId + '/follow-ups';
    document.getElementById('followUpTaskTitle').textContent = taskTitle;
    document.getElementById('followUpMachineName').textContent = machineName || 'لا يوجد';
    modal.classList.remove('hidden');
}

function closeFollowUpModal() {
    const modal = document.getElementById('followUpModal');
    if (modal) modal.classList.add('hidden');
}

function openUpdateStatusModal(taskId, status, priority, notes) {
    const modal = document.getElementById('updateStatusModal');
    const form = document.getElementById('updateStatusForm');
    if (!modal || !form) return;

    form.action = '/tasks/' + taskId;
    document.getElementById('edit_status').value = status || 'in_progress';
    document.getElementById('edit_priority').value = priority || 'high';
    document.getElementById('edit_notes').value = notes || '';
    modal.classList.remove('hidden');
}

function closeUpdateStatusModal() {
    const modal = document.getElementById('updateStatusModal');
    if (modal) modal.classList.add('hidden');
}

window.addEventListener('click', function(e) {
    const newModal = document.getElementById('newTaskModal');
    const fuModal = document.getElementById('followUpModal');
    const usModal = document.getElementById('updateStatusModal');
    if (e.target === newModal) closeNewTaskModal();
    if (e.target === fuModal) closeFollowUpModal();
    if (e.target === usModal) closeUpdateStatusModal();
});

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewTaskModal();
        closeFollowUpModal();
        closeUpdateStatusModal();
    }
});
</script>
@endsection
