@extends('layouts.app')

@section('title', 'تفاصيل خط التصنيع: ' . $factory->name . ' - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center text-xl font-bold shrink-0">
                    <i class="fa-solid fa-industry"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-slate-800">{{ $factory->name }}</h1>
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700">{{ $factory->code }}</span>
                        @if($factory->is_outsource)
                            <span class="badge badge-warning text-xs">تصنيع عند الغير (Outsource)</span>
                        @else
                            <span class="badge badge-success text-xs">خط داخلي مملوك للشركة</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ $factory->description }} | المشرف المسؤول: <strong class="text-slate-700">{{ $factory->supervisor?->name ?? 'إدارة التشغيل المركزية' }}</strong></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('operations.hourlyReports') }}" class="btn-primary text-xs flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل تقرير ساعة تشغيل</span>
            </a>
            <a href="{{ route('factories.index') }}" class="btn-secondary text-xs flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                <span>كل المصانع</span>
            </a>
        </div>
    </div>

    <!-- Financial Performance Cards (P&L for this factory line) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي التوريد للمخزن (+14%)</span>
            <div class="font-mono font-bold text-2xl text-slate-800">{{ number_format($totalInternalRevenue, 2) }} <span class="text-xs text-slate-500 font-sans">ج.م</span></div>
            <span class="text-[11px] text-slate-400 mt-1 block">مبيعات داخلية للمخزن المركزي</span>
        </div>

        <div class="card p-5">
            <span class="text-xs font-bold text-emerald-600 block mb-1">هامش ربح الـ 14% المحقق</span>
            <div class="font-mono font-bold text-2xl text-emerald-600">+{{ number_format($totalMarkup14, 2) }} <span class="text-xs text-slate-500 font-sans">ج.م</span></div>
            <span class="text-[11px] text-emerald-600 mt-1 block">قيمة مضافة للمصنع كمركز ربحية</span>
        </div>

        <div class="card p-5">
            <span class="text-xs font-bold text-rose-600 block mb-1">تكاليف التشغيل والخامات</span>
            <div class="font-mono font-bold text-2xl text-rose-600">-{{ number_format($totalCosts, 2) }} <span class="text-xs text-slate-500 font-sans">ج.م</span></div>
            <span class="text-[11px] text-rose-400 mt-1 block">أجور، طاقة، خامات، وصيانة</span>
        </div>

        <div class="card p-5">
            <span class="text-xs font-bold text-slate-400 block mb-1">صافي ربح الخط التشغيلي</span>
            <div class="font-mono font-bold text-2xl {{ $netProfit >= 0 ? 'text-primary-700' : 'text-rose-600' }}">
                {{ $netProfit >= 0 ? '+' : '' }}{{ number_format($netProfit, 2) }} <span class="text-xs text-slate-500 font-sans">ج.م</span>
            </div>
            <span class="text-[11px] {{ $netProfit >= 0 ? 'text-primary-600' : 'text-rose-500' }} mt-1 block">المركز المالي المستقل للخط</span>
        </div>
    </div>

    <!-- Machines Assigned to This Line -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-gears text-primary-600"></i>
            <span>الماكينات وخطوط الإنتاج التابعة ({{ $factory->machines->count() }})</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($factory->machines as $mach)
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200">{{ $mach->code }}</span>
                        @if($mach->status == 'running')
                            <span class="badge badge-success text-[10px]">قيد التشغيل</span>
                        @elseif($mach->status == 'maintenance')
                            <span class="badge badge-warning text-[10px]">صيانة</span>
                        @else
                            <span class="badge badge-danger text-[10px]">متوقفة</span>
                        @endif
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm">{{ $mach->name }}</h4>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $mach->model ?? 'طراز صناعي' }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/80 grid grid-cols-2 gap-2 text-xs font-mono">
                    <div>
                        <span class="text-slate-400 font-sans text-[11px] block">السرعة المقدرة:</span>
                        <strong class="text-slate-700">{{ number_format($mach->rated_speed_units_per_hour) }} ق/ساعة</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans text-[11px] block">سرعة الماكينة:</span>
                        <strong class="text-slate-700">{{ $mach->belt_speed_m_min }} قطعة/دقيقة</strong>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 py-6 text-center text-slate-400 text-xs">لا توجد ماكينات مسجلة تحت هذا المصنع بعد.</div>
            @endforelse
        </div>
    </div>

    <!-- Recent Hourly Reports & AI Advice -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary-600"></i>
                <span>تقارير الساعة التشغيلية وتوصيات الذكاء الاصطناعي</span>
            </h2>
            <span class="text-xs text-slate-400">آخر 15 تقرير ساعة تشغيل</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <th class="py-3 px-4">التاريخ والساعة</th>
                        <th class="py-3 px-4">الماكينة</th>
                        <th class="py-3 px-4">الإنتاج الفعلي</th>
                        <th class="py-3 px-4">سرعة السير الحالية</th>
                        <th class="py-3 px-4">كفاءة الماكينة (OEE)</th>
                        <th class="py-3 px-4">دقائق التوقف</th>
                        <th class="py-3 px-4">توصيات الذكاء الاصطناعي لاتخاذ القرار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($recentHourlyReports as $report)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-700">{{ $report->report_date?->format('Y-m-d') }}</span>
                            <span class="block text-[11px] text-slate-400 font-sans">الساعة {{ $report->hour_interval }}:00</span>
                        </td>
                        <td class="py-3 px-4 font-sans font-semibold text-slate-800">
                            {{ $report->machine?->name ?? 'ماكينة عامة' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800 text-sm">
                            {{ number_format($report->units_produced) }} <span class="text-[10px] text-slate-400 font-sans">قطعة</span>
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $report->belt_speed_actual }} م/دقيقة
                        </td>
                        <td class="py-3 px-4">
                            @if($report->efficiency_rate >= 90)
                                <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">{{ number_format($report->efficiency_rate, 1) }}%</span>
                            @elseif($report->efficiency_rate >= 75)
                                <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded">{{ number_format($report->efficiency_rate, 1) }}%</span>
                            @else
                                <span class="text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded">{{ number_format($report->efficiency_rate, 1) }}%</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($report->downtime_minutes > 0)
                                <span class="text-rose-600 font-bold">{{ $report->downtime_minutes }} دقيقة</span>
                                <span class="block text-[10px] text-slate-500 font-sans line-clamp-1">{{ $report->downtime_reason }}</span>
                            @else
                                <span class="text-slate-400">0 دقيقة</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-sans text-xs">
                            @if($report->ai_recommendation)
                                <div class="p-2 bg-primary-50/60 rounded-lg border border-primary-100 text-primary-900 flex items-start gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles text-primary-600 mt-0.5 shrink-0 text-xs"></i>
                                    <span>{{ $report->ai_recommendation }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">مستقر ومطابق لمعدل السرعة القياسي</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-sans">لا توجد تقارير ساعات مسجلة لهذا الخط حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
