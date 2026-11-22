@extends('layouts.app')

@section('title', 'إدارة الصيانة والماكينات وقطع الغيار - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">إدارة الصيانة والماكينات وقطع الغيار</h1>
            <p class="text-sm text-slate-500 mt-1">متابعة كفاءة وسرعات ماكينات الإنتاج والتوقفات وجرد مخزون قطع الغيار الحيوية</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('addMachineModal').classList.remove('hidden')" class="btn-primary flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة ماكينة إنتاج جديدة</span>
            </button>
        </div>
    </div>

    <!-- Status Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $runningCount = $machines->where('status', 'running')->count();
            $idleCount = $machines->where('status', 'idle')->count();
            $maintCount = $machines->where('status', 'maintenance')->count();
            $breakdownCount = $machines->where('status', 'breakdown')->count();
        @endphp
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400">الماكينات قيد التشغيل</span>
                    <div class="font-mono font-bold text-2xl text-emerald-600 mt-1">{{ $runningCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-circle-play"></i>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400">ماكينات جاهزة (وضع استعداد)</span>
                    <div class="font-mono font-bold text-2xl text-slate-700 mt-1">{{ $idleCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-pause"></i>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400">تحت الصيانة الوقائية</span>
                    <div class="font-mono font-bold text-2xl text-amber-600 mt-1">{{ $maintCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wrench"></i>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400">أعطال وتوقفات حرجة</span>
                    <div class="font-mono font-bold text-2xl text-rose-600 mt-1">{{ $breakdownCount }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Machines List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-gears text-primary-600"></i>
                <span>ماكينات خطوط إنتاج الفا ايجبت ({{ $machines->count() }})</span>
            </h2>
            <span class="text-xs text-slate-400">سرعات السير، الطاقة التصميمية، وتحديث الحالة</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600 font-bold">
                        <th class="py-4 px-6">كود واسم الماكينة</th>
                        <th class="py-4 px-6">المصنع / الخط</th>
                        <th class="py-4 px-6">السرعة التصميمية</th>
                        <th class="py-4 px-6">سرعة السير القياسية</th>
                        <th class="py-4 px-6">الحالة الفنية</th>
                        <th class="py-4 px-6">موعد الصيانة القادم</th>
                        <th class="py-4 px-6 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($machines as $mach)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-microchip"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 block text-base">{{ $mach->name }}</span>
                                    <span class="text-xs font-mono text-slate-400">{{ $mach->code }} | {{ $mach->model ?? 'طراز صناعي' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-700 text-xs">
                            {{ $mach->factory?->name ?? 'خط غير محدد' }}
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-800 text-sm">
                            {{ number_format($mach->rated_speed_units_per_hour) }} <span class="text-xs text-slate-400 font-sans">وحدة/ساعة</span>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-700 text-sm">
                            {{ $mach->belt_speed_m_min }} <span class="text-xs text-slate-400 font-sans">م/دقيقة</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($mach->status == 'running')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> تعمل بكفاءة</span>
                            @elseif($mach->status == 'maintenance')
                                <span class="badge badge-warning"><i class="fa-solid fa-wrench"></i> تحت الصيانة</span>
                            @elseif($mach->status == 'idle')
                                <span class="badge badge-neutral"><i class="fa-solid fa-pause"></i> جاهزة / متوقفة مؤقتاً</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> عطل حرج</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-mono text-xs text-slate-600">
                            {{ $mach->maintenance_due_date ? $mach->maintenance_due_date->format('Y-m-d') : 'غير محدد' }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            <button type="button" onclick="document.getElementById('statusModal-{{ $mach->id }}').classList.remove('hidden')" class="btn-secondary text-xs flex items-center gap-1.5 mx-auto">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>تحديث الحالة</span>
                            </button>
                        </td>
                    </tr>

                    <!-- Status Update Modal -->
                    <div id="statusModal-{{ $mach->id }}" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
                        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
                            <form action="{{ route('maintenance.machines.status', $mach->id) }}" method="POST">
                                @csrf
                                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-base">تحديث الحالة: {{ $mach->name }}</h3>
                                    <button type="button" onclick="document.getElementById('statusModal-{{ $mach->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">الحالة التشغيلية</label>
                                        <select name="status" class="input-field">
                                            <option value="running" {{ $mach->status == 'running' ? 'selected' : '' }}>تعمل بكفاءة (Running)</option>
                                            <option value="idle" {{ $mach->status == 'idle' ? 'selected' : '' }}>جاهزة / متوقفة مؤقتاً (Idle)</option>
                                            <option value="maintenance" {{ $mach->status == 'maintenance' ? 'selected' : '' }}>صيانة وقائية / دورية (Maintenance)</option>
                                            <option value="breakdown" {{ $mach->status == 'breakdown' ? 'selected' : '' }}>عطل فني / توقف طارئ (Breakdown)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">موعد الصيانة القادم</label>
                                        <input type="date" name="maintenance_due_date" value="{{ $mach->maintenance_due_date?->format('Y-m-d') }}" class="input-field font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات وتقرير الفني</label>
                                        <textarea name="notes" rows="2" placeholder="ملاحظات حول حالة السيور، التروس، السخانات، أو الزيوت..." class="input-field">{{ $mach->notes }}</textarea>
                                    </div>
                                </div>
                                <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('statusModal-{{ $mach->id }}').classList.add('hidden')" class="btn-secondary text-xs">إلغاء</button>
                                    <button type="submit" class="btn-primary text-xs">حفظ الحالة</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Spare Parts Inventory Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-primary-600"></i>
                <span>مخزون قطع الغيار الحيوية (المخزن المركزي)</span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                            <th class="py-2.5 px-3">القطعة</th>
                            <th class="py-2.5 px-3">المخزون الحالي</th>
                            <th class="py-2.5 px-3">حد الأمان</th>
                            <th class="py-2.5 px-3">سعر الوحدة</th>
                            <th class="py-2.5 px-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse($spareParts as $part)
                        @php
                            $isLow = $part->current_stock <= $part->min_stock_level;
                        @endphp
                        <tr class="{{ $isLow ? 'bg-amber-50/50' : '' }}">
                            <td class="py-2.5 px-3 font-sans font-bold text-slate-800">
                                {{ $part->name }}
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $part->code }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ number_format($part->current_stock) }} {{ $part->unit }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500">
                                {{ number_format($part->min_stock_level) }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ number_format($part->cost_price, 2) }} ج.م
                            </td>
                            <td class="py-2.5 px-3 font-sans">
                                @if($isLow)
                                    <span class="badge badge-warning text-[10px]">نقص بالمخزون</span>
                                @else
                                    <span class="badge badge-success text-[10px]">متوفر</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400 font-sans">لا توجد قطع غيار مسجلة بعد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Downtime Breakdown -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-rose-600"></i>
                <span>سجل التوقفات والأعطال الأخيرة</span>
            </h2>

            <div class="space-y-3">
                @forelse($recentDowntime as $down)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-800 text-xs">{{ $down->machine?->name ?? 'ماكينة' }}</h4>
                            <span class="text-[10px] text-slate-400">({{ $down->machine?->factory?->name }})</span>
                        </div>
                        <p class="text-xs text-rose-700 mt-1 font-semibold">{{ $down->downtime_reason ?? 'توقف صيانة مفاجئ' }}</p>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $down->report_date?->format('Y-m-d') }} - الساعة {{ $down->hour_interval }}:00</span>
                    </div>
                    <div class="text-left font-mono shrink-0">
                        <span class="px-2 py-1 rounded bg-rose-100 text-rose-700 font-bold text-xs">{{ $down->downtime_minutes }} دقيقة</span>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-check-double text-emerald-500 text-2xl mb-2 block"></i>
                    لا توجد توقفات أو أعطال مسجلة مؤخراً. خطوط الإنتاج تعمل باستقرار تام.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Machine Modal -->
    <div id="addMachineModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <form action="{{ route('maintenance.machines.store') }}" method="POST">
                @csrf
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-lg">تسجيل ماكينة إنتاج جديدة</h3>
                    <button type="button" onclick="document.getElementById('addMachineModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">المصنع / الخط التابع له <span class="text-rose-500">*</span></label>
                        <select name="factory_id" required class="input-field">
                            @foreach($factories as $f)
                                <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم الماكينة <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="مثال: ماكينة تعبئة كوفير 4*1" class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">كود الماكينة <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" required placeholder="مثال: MCH-CVR-02" class="input-field font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الموديل والطراز</label>
                            <input type="text" name="model" placeholder="Model 2026" class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الرقم التسلسلي</label>
                            <input type="text" name="serial_number" placeholder="SN-892348" class="input-field font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">السرعة التصميمية (قطعة/ساعة) <span class="text-rose-500">*</span></label>
                            <input type="number" name="rated_speed_units_per_hour" required min="1" placeholder="3000" class="input-field font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">سرعة السير (متر/دقيقة) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.1" name="belt_speed_m_min" required min="0.1" placeholder="25.0" class="input-field font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">موعد الصيانة الدورية القادم</label>
                        <input type="date" name="maintenance_due_date" class="input-field font-mono">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('addMachineModal').classList.add('hidden')" class="btn-secondary text-xs">إلغاء</button>
                    <button type="submit" class="btn-primary text-xs">تسجيل الماكينة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
