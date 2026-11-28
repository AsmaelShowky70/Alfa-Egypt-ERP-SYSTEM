@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة الصيانة والمعدات')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-orange-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-orange-500/20 text-orange-300 border border-orange-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                الصيانة الهندسية والمرافق
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا فادي أشرف (مدير الصيانة)</h1>
            <p class="text-orange-100 text-sm max-w-xl">
                متابعة الحالة التشغيلية لماكينات خطوط الإنتاج والسيور، أرصدة قطع الغيار في المخزن المركزي، وتحليل أسباب التوقفات المسجلة في تقارير الساعات.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('maintenance.index') }}" class="px-5 py-2.5 bg-orange-400 hover:bg-orange-300 text-orange-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-wrench"></i>
                <span>سجل الماكينات والجاهزية</span>
            </a>
            <a href="{{ route('warehouse.spareParts') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>مخزون قطع الغيار</span>
            </a>
        </div>
    </div>

    <!-- Maintenance KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">ماكينات تعمل بحالة ممتازة</p>
                <h3 class="text-xl font-black text-emerald-700">{{ $runningMachines }} <span class="text-xs font-medium text-slate-500">ماكينة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">ماكينات تتطلب صيانة دورية</p>
                <h3 class="text-xl font-black text-orange-700">{{ $maintenanceDueMachines }} <span class="text-xs font-medium text-slate-500">ماكينة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-toolbox"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">أنواع قطع الغيار المتوفرة</p>
                <h3 class="text-xl font-black text-slate-800">{{ $spareParts->count() }} <span class="text-xs font-medium text-slate-500">بند</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border {{ $lowSpareParts->count() > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lowSpareParts->count() > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قطع غيار أوشكت على النفاد</p>
                <h3 class="text-xl font-black {{ $lowSpareParts->count() > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lowSpareParts->count() }} <span class="text-xs font-medium text-slate-500">بند</span></h3>
            </div>
        </div>
    </div>

    <!-- Delegated Glove Production Section (عمل إضافي تشغيلي من مدير التشغيل) -->
    <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-slate-50 border border-amber-200/80 rounded-3xl p-6 shadow-sm space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-amber-200/60 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                            تكليف تشغيلي إضافي
                        </span>
                        <span class="text-xs text-slate-500 font-bold">بتكليف من مدير التشغيل (تخفيف العبء)</span>
                    </div>
                    <h2 class="text-lg font-black text-slate-900 mt-0.5">متابعة تشغيل وإنتاجية ماكينات الجوانتي (قص وتغليف)</h2>
                    <p class="text-xs text-slate-600">متابعة الإنتاج الفعلي، ونسب خروج الجوانتيات والباكتات، وسرعات الماكينات وتقارير الساعات</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openGloveHourlyModal()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>تسجيل تقرير إنتاج ساعة للجوانتي</span>
                </button>
            </div>
        </div>

        <!-- Glove Production KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-slate-400 block font-bold text-[11px]">إنتاج ماكينة القص (CUT-01)</span>
                    <span class="text-xl font-black text-slate-900 block mt-1">{{ number_format($gloveCuttingUnitsToday) }}</span>
                    <span class="text-[10px] text-amber-600 font-bold">جوانتي مقصوص اليوم</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-scissors"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-slate-400 block font-bold text-[11px]">إنتاج ماكينة التغليف (PCK-01)</span>
                    <span class="text-xl font-black text-slate-900 block mt-1">{{ number_format($glovePackingUnitsToday) }}</span>
                    <span class="text-[10px] text-emerald-600 font-bold">باكت معبأ (100 ق)</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-slate-400 block font-bold text-[11px]">متوسط كفاءة التشغيل</span>
                    <span class="text-xl font-black text-blue-700 block mt-1">{{ number_format($gloveAvgEfficiency, 1) }}%</span>
                    <span class="text-[10px] text-blue-500 font-bold">توقفات اليوم: {{ $gloveDowntimeToday }} د</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
            </div>
        </div>

        <!-- Today's Glove Reports Table -->
        @if($gloveHourlyReportsToday->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800">تقارير ساعات إنتاج الجوانتي لليوم ({{ $gloveHourlyReportsToday->count() }})</span>
                <span class="text-[10px] text-slate-500 font-medium">سجل المتابعة الإنتاجية اللحظي</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100">
                            <th class="py-2.5 px-3">الماكينة</th>
                            <th class="py-2.5 px-3">الساعة</th>
                            <th class="py-2.5 px-3">الوردية</th>
                            <th class="py-2.5 px-3">الإنتاج</th>
                            <th class="py-2.5 px-3">السرعة</th>
                            <th class="py-2.5 px-3">الكفاءة</th>
                            <th class="py-2.5 px-3">الهدر</th>
                            <th class="py-2.5 px-3">الملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($gloveHourlyReportsToday as $grep)
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-slate-800">{{ $grep->machine->name }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold">{{ $grep->hour_slot }}</td>
                            <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] {{ $grep->shift_badge_class }}">{{ $grep->shift_label }}</span></td>
                            <td class="py-2.5 px-3 font-mono font-bold text-amber-700">{{ number_format($grep->units_produced) }}</td>
                            <td class="py-2.5 px-3 font-mono">{{ $grep->belt_speed_m_min }} م/د</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-blue-600">{{ number_format($grep->efficiency_rate, 1) }}%</td>
                            <td class="py-2.5 px-3 font-mono text-rose-600">{{ $grep->waste_units }}</td>
                            <td class="py-2.5 px-3 text-slate-600 truncate max-w-xs">{{ $grep->actions_taken ?? 'تشغيل منتظم' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Modal: Add Glove Hourly Report -->
    <div id="gloveHourlyModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                    <span>تسجيل تقرير إنتاج ساعة - ماكينات الجوانتي</span>
                </h3>
                <button type="button" onclick="closeGloveHourlyModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('operations.hourlyReports.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="operator_name" value="{{ Auth::user()->name }}">
                <input type="hidden" name="report_date" value="{{ now()->toDateString() }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ماكينة الجوانتي *</label>
                    <select name="machine_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-amber-600 outline-none">
                        @if($gloveCutMachine)
                            <option value="{{ $gloveCutMachine->id }}">ماكينة قص الجوانتيات الدوارة (MCH-GLV-CUT01)</option>
                        @endif
                        @if($glovePckMachine)
                            <option value="{{ $glovePckMachine->id }}">ماكينة باكت وتغليف الجوانتيات (MCH-GLV-PCK01)</option>
                        @endif
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الوردية *</label>
                        <select name="shift" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-amber-600 outline-none">
                            <option value="1">الوردية الأولى (الصباحية)</option>
                            <option value="2">الوردية الثانية (المسائية)</option>
                            <option value="3">الوردية الثالثة (الليلية)</option>
                            <option value="custom">وردية إضافية / أوفر تايم</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الساعة الزمنية *</label>
                        <input type="text" name="hour_slot" value="{{ now()->format('H:00') }} - {{ now()->addHour()->format('H:00') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الإنتاج الفعلي (قطعة/باكت) *</label>
                        <input type="number" name="units_produced" value="4500" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">سرعة السير (م/دقيقة) *</label>
                        <input type="number" step="0.1" name="belt_speed_m_min" value="30.0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الهدر (وحدات تالفة)</label>
                        <input type="number" name="waste_units" value="10" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">دقائق التوقف (إن وجدت)</label>
                        <input type="number" name="downtime_minutes" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات الإنتاج والتشغيل</label>
                    <textarea name="actions_taken" rows="2" placeholder="ملاحظات سرعة السحب، ضغط الهواء، جودة التغليف..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-amber-600 outline-none">تم فحص جودة القص واللحام، والماكينة تعمل بكفاءة إنتاجية عالية.</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeGloveHourlyModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow">اعتماد وحفظ تقرير الإنتاج</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openGloveHourlyModal() {
        const modal = document.getElementById('gloveHourlyModal');
        if (modal) modal.classList.remove('hidden');
    }
    function closeGloveHourlyModal() {
        const modal = document.getElementById('gloveHourlyModal');
        if (modal) modal.classList.add('hidden');
    }
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('gloveHourlyModal');
        if (e.target === modal) closeGloveHourlyModal();
    });
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeGloveHourlyModal();
    });
    </script>

    <!-- Machines Grid -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-industry text-orange-600"></i>
                <span>جاهزية ماكينات وخطوط ألفا إيجبت</span>
            </h2>
            <a href="{{ route('maintenance.index') }}" class="text-xs font-bold text-orange-600 hover:underline">عرض التفاصيل الكاملة</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($machines as $m)
                <div class="p-4 rounded-2xl border border-slate-200/90 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-slate-400">{{ $m->code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $m->status === 'running' ? 'تعمل بكفاءة' : 'في الصيانة' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm text-slate-800">{{ $m->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $m->factory->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-center text-xs bg-white p-2 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block">السرعة المعيارية</span>
                            <span class="font-black text-slate-800">{{ number_format($m->rated_speed_units_per_hour) }} ق/س</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">سرعة الماكينة</span>
                            <span class="font-black text-slate-800">{{ $m->belt_speed_m_min }} ق/د</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
