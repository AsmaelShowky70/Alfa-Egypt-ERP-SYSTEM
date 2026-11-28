@extends('layouts.app')

@section('title', 'لوحة إشراف وتشغيل خط ماكينة الوايبس - جرجس غطاس')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Profile & Machine Status -->
    <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <i class="fa-solid fa-soap text-9xl"></i>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-5">
                <div class="relative">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-20 h-20 rounded-2xl object-cover border-4 border-emerald-400/40 shadow-xl">
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            متابعة إنتاج وتشغيل (بتكليف من مدير التشغيل)
                        </span>
                        <span class="text-xs text-slate-300 font-mono">{{ $wipesMachine?->code ?? 'MCH-WIPES-01' }}</span>
                    </div>
                    <h1 class="text-2xl font-black text-white mt-1">{{ Auth::user()->name }}</h1>
                    <p class="text-xs text-emerald-200 mt-0.5">متابعة الإنتاج اليومي وتدفق المحلول والهدر: {{ $wipesMachine?->name ?? 'ماكينة المناديل المعطرة والوايبس الأوتوماتيكية' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ ($wipesMachine?->status ?? 'running') === 'running' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></div>
                    <div class="text-right">
                        <span class="text-[10px] text-emerald-200 block font-bold">حالة الماكينة الحالية</span>
                        <span class="text-xs font-black text-white">
                            {{ ($wipesMachine?->status ?? 'running') === 'running' ? 'تعمل بكامل طاقتها' : 'تحت الصيانة' }}
                        </span>
                    </div>
                </div>

                <button type="button" onclick="openHourlyModal()" class="px-5 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-2xl text-xs shadow-lg transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>تسجيل تقرير ساعة للماكينة</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Operational KPIs for Wipes Machine -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold block">إنتاج الوايبس اليوم</span>
                <span class="text-2xl font-black text-slate-900">{{ number_format($totalProducedToday) }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold block">منديل / عبوة</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold block">معدل الكفاءة</span>
                <span class="text-2xl font-black text-blue-700">{{ number_format($avgEfficiency, 1) }}%</span>
                <span class="text-[10px] text-blue-500 font-semibold block">السرعة المقدرة 5,000/س</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold block">دقائق التوقف</span>
                <span class="text-2xl font-black text-amber-700">{{ $downtimeToday }}</span>
                <span class="text-[10px] text-amber-600 font-semibold block">دقيقة اليوم</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold block">إجمالي الهدر</span>
                <span class="text-2xl font-black text-rose-700">{{ number_format($wasteUnitsToday) }}</span>
                <span class="text-[10px] text-rose-500 font-semibold block">وحدة معيبة</span>
            </div>
        </div>
    </div>

    <!-- Active Tasks for Gerges Ghatas -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-purple-600"></i>
                <span>التكليف الإداري المباشر لماكينة الوايبس</span>
            </h3>
            <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-purple-600 hover:underline">
                عرض تفاصيل التكليفات والتقارير →
            </a>
        </div>

        @forelse($myTasks as $task)
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border {{ $task->task_type_badge_class }}">
                                {{ $task->task_type_arabic_name }}
                            </span>
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border {{ $task->priority_badge_class }}">
                                {{ $task->priority_arabic_name }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm mt-1.5">{{ $task->title }}</h4>
                    </div>

                    <a href="{{ route('tasks.index') }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 self-start">
                        <i class="fa-solid fa-clipboard-check text-xs"></i>
                        <span>تسجيل تقرير متابعة فني</span>
                    </a>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">{{ $task->description }}</p>

                @if($task->followUps->isNotEmpty())
                    <div class="bg-white p-3 rounded-xl border border-slate-200 text-xs text-slate-700 space-y-1">
                        <span class="font-bold text-slate-900 block text-[11px] text-purple-700">آخر تقرير متابعة مسجل:</span>
                        <p class="text-slate-600 font-medium">{{ $task->followUps->first()->notes }}</p>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-xs text-slate-500">لا توجد تكليفات إضافية معلقة حالياً.</p>
        @endforelse
    </div>

    <!-- Today's Hourly Machine Reports for MCH-WIPES-01 -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    <span>سجل تقارير الساعات اليومية لماكينة الوايبس ({{ $todayReports->count() }} تقرير)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">معدل تدفق سائل التعطير، جودة لحام أكياس الفويل، وسرعات الماكينة الفعلية</p>
            </div>

            <button type="button" onclick="openHourlyModal()" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة ساعة جديدة</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <th class="py-3 px-4">الساعة</th>
                        <th class="py-3 px-4">الوردية</th>
                        <th class="py-3 px-4">الإنتاج الفعلي</th>
                        <th class="py-3 px-4">سرعة السير</th>
                        <th class="py-3 px-4">الكفاءة</th>
                        <th class="py-3 px-4">الهدر</th>
                        <th class="py-3 px-4">التوقف (دقيقة)</th>
                        <th class="py-3 px-4">ملاحظات التشغيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayReports as $rep)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $rep->hour_slot }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rep->shift_badge_class }}">
                                    {{ $rep->shift_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-black text-emerald-700 text-sm">{{ number_format($rep->units_produced) }} ق</td>
                            <td class="py-3 px-4 font-mono">{{ $rep->belt_speed_m_min }} م/د</td>
                            <td class="py-3 px-4 font-mono font-bold {{ $rep->efficiency_rate >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ number_format($rep->efficiency_rate, 1) }}%
                            </td>
                            <td class="py-3 px-4 font-mono text-rose-600 font-semibold">{{ $rep->waste_units }} ق</td>
                            <td class="py-3 px-4 font-mono text-amber-600 font-semibold">{{ $rep->downtime_minutes }} د</td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate">{{ $rep->actions_taken ?? $rep->downtime_reason ?? 'تشغيل منتظم' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                لا توجد تقارير ساعات مسجلة لليوم حتى الآن. انقر على "تسجيل تقرير ساعة للماكينة" للبدء.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Modal: Add Hourly Report for Wipes Machine -->
    <div id="hourlyReportModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    <span>تسجيل تقرير ساعة - ماكينة الوايبس</span>
                </h3>
                <button type="button" onclick="closeHourlyModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('operations.hourlyReports.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="machine_id" value="{{ $wipesMachine?->id }}">
                <input type="hidden" name="operator_name" value="{{ Auth::user()->name }}">
                <input type="hidden" name="report_date" value="{{ now()->toDateString() }}">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الوردية *</label>
                        <select name="shift" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-emerald-600 outline-none">
                            <option value="1">الوردية الأولى (الصباحية)</option>
                            <option value="2">الوردية الثانية (المسائية)</option>
                            <option value="3">الوردية الثالثة (الليلية)</option>
                            <option value="custom">وردية إضافية / خاصة</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الساعة الزمنية *</label>
                        <input type="text" name="hour_slot" value="{{ now()->format('H:00') }} - {{ now()->addHour()->format('H:00') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الإنتاج الفعلي (قطعة) *</label>
                        <input type="number" name="units_produced" value="5000" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">سرعة السير (متر/دقيقة)</label>
                        <input type="number" step="0.1" name="belt_speed_m_min" value="25.0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الهدر (وحدات تالفة)</label>
                        <input type="number" name="waste_units" value="15" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">دقائق التوقف (إن وجدت)</label>
                        <input type="number" name="downtime_minutes" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات التشغيل والتحكم في المحلول المعطر</label>
                    <textarea name="actions_taken" rows="2" placeholder="ملاحظات ضبط السائل، حرارة لحام الفويل، عينات الفحص..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">تدفق محلول التعطير منتظم، درجة حرارة لحام الفويل الفضي 140 درجة، الفحص البصري سليم 100%.</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeHourlyModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow">اعتماد وحفظ تقرير الساعة</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openHourlyModal() {
    const modal = document.getElementById('hourlyReportModal');
    if (modal) modal.classList.remove('hidden');
}

function closeHourlyModal() {
    const modal = document.getElementById('hourlyReportModal');
    if (modal) modal.classList.add('hidden');
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('hourlyReportModal');
    if (e.target === modal) closeHourlyModal();
});

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeHourlyModal();
});
</script>
@endsection
