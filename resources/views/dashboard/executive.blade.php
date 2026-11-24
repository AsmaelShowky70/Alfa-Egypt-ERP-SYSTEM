@extends('layouts.app')

@section('title', 'لوحة تحكم الإدارة العليا')

@section('content')
<div class="space-y-8">

    <!-- Welcome & Executive Status -->
    <div class="bg-gradient-to-r from-black via-zinc-950 to-neutral-900 border-r-4 border-red-600 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <div class="inline-flex items-center gap-2 bg-red-600/20 text-red-400 border border-red-500/30 px-3.5 py-1.5 rounded-full text-xs font-bold">
                <i class="fa-solid fa-crown text-amber-400"></i>
                لوحة القيادة الاستراتيجية — {{ Auth::user()->role_arabic_name }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً، {{ Auth::user()->name }}</h1>
            <p class="text-zinc-300 text-sm max-w-2xl leading-relaxed">
                متابعة شاملة لأداء مصانع ألفا إيجبت الـ 6 المستقلة، مراكز الربحية وهامش الـ 14%، حركة المخازن المركزية، وتدفق أوامر التشغيل والتقارير اللحظية للماكينات.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('factories.index') }}" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-red-600/30 transition flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i>
                <span>تقرير أرباح المصانع الـ 6</span>
            </a>
            <a href="{{ route('operations.hourlyReports') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-clock"></i>
                <span>تقارير الماكينات كل ساعة</span>
            </a>
        </div>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-brand-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي إيرادات المبيعات المحصلة</p>
                <h3 class="text-xl font-black text-slate-800">{{ number_format($totalSalesRevenue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
                <span class="text-[11px] text-emerald-600 font-semibold"><i class="fa-solid fa-arrow-trend-up"></i> تدفق نقدي ممتاز</span>
            </div>
        </div>

        <!-- Factory 14% Markups -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">أرباح تشغيل الخطوط (14% داخلي)</p>
                <h3 class="text-xl font-black text-indigo-700">{{ number_format($totalFactoryMarkup14, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
                <span class="text-[11px] text-indigo-600 font-semibold">محملة على المخزن المركزي</span>
            </div>
        </div>

        <!-- Central Warehouse Value -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قيمة مخزون المخزن المركزي</p>
                <h3 class="text-xl font-black text-slate-800">{{ number_format($totalWarehouseValue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
                <span class="text-[11px] text-amber-600 font-semibold">خامات + منتج تام + قطع غيار</span>
            </div>
        </div>

        <!-- Loss Alarms Counter -->
        <div class="bg-white rounded-2xl p-5 border {{ $lossAlarmsCount > 0 ? 'border-rose-300 bg-rose-50/20 ring-2 ring-rose-500/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl {{ $lossAlarmsCount > 0 ? 'bg-rose-100 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إنذارات السعر أقل من التكلفة</p>
                <h3 class="text-xl font-black {{ $lossAlarmsCount > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lossAlarmsCount }} <span class="text-xs font-medium text-slate-500">عقد/طلب</span></h3>
                @if($lossAlarmsCount > 0)
                    <a href="{{ route('finance.lossAlarms') }}" class="text-[11px] text-rose-600 font-bold hover:underline">مراجعة العقود الخاسرة فورا →</a>
                @else
                    <span class="text-[11px] text-emerald-600 font-semibold">جميع الأسعار أعلى من التكلفة</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Factory Independence & Profit Centers Grid (المصانع الـ 6 كمراكز أرباح مستقلة) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-industry text-brand-600"></i>
                    <span>مراكز ربحية خطوط الإنتاج والمصانع المستقلة (الـ 6 خطوط)</span>
                </h2>
                <p class="text-xs text-slate-500">معاملة كل خط كمصنع مستقل لتحديد نقاط القوة والضعف وأرباح البيع الداخلي والخارجي</p>
            </div>
            <a href="{{ route('factories.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                <span>عرض تحليل المصانع التفصيلي</span>
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($factories as $f)
                @php
                    $perf = collect($factoryPerformance)->firstWhere('code', $f->code) ?? ['revenue' => 0, 'cost' => 0, 'profit' => 0];
                @endphp
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $f->type === 'internal' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $f->type === 'internal' ? 'تصنيع داخلي' : 'تصنيع عند الغير (خارجي)' }}
                            </span>
                            <h3 class="font-bold text-sm text-slate-800 mt-1">{{ $f->name }}</h3>
                            <p class="text-[11px] text-slate-400 font-medium">المشرف: {{ $f->supervisor_name ?? 'غير محدد' }}</p>
                        </div>
                        <span class="text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-xl">
                            +{{ $f->markup_percentage }}% تكلفة إنتاج
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">إجمالي التوريدات</span>
                            <span class="font-extrabold text-slate-800">{{ number_format($perf['revenue'], 0) }} ج.م</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">صافي الربح / المساهمة</span>
                            <span class="font-extrabold {{ $perf['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format($perf['profit'], 0) }} ج.م
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 text-slate-500">
                        <span>الماكينات: <b>{{ $f->machines_count }}</b></span>
                        <a href="{{ route('factories.show', $f->id) }}" class="font-bold text-brand-600 hover:underline">
                            الملف والأرباح →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Charts & Hourly Machine Logs (كل ساعة) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Hourly Reports Stream with Smart AI Decisions -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                        <span>تقارير الماكينات كل ساعة (مرفوعة للإدارة العليا)</span>
                    </h2>
                    <p class="text-xs text-slate-500">بيانات السرعة، الكفاءة OEE، والتوصيات التشغيلية الذكية لاتخاذ القرار</p>
                </div>
                <a href="{{ route('operations.hourlyReports') }}" class="text-xs font-bold text-brand-600 hover:underline">عرض السجل الكامل</a>
            </div>

            <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                @forelse($hourlyReportsToday as $rep)
                    <div class="p-3.5 rounded-2xl border {{ $rep->downtime_minutes > 0 ? 'border-amber-200 bg-amber-50/40' : 'border-slate-100 bg-slate-50/60' }} space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900">{{ $rep->machine->name }}</span>
                                <span class="bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded text-[10px]">{{ $rep->hour_slot }}</span>
                            </div>
                            <span class="font-extrabold {{ $rep->efficiency_rate >= 90 ? 'text-emerald-600' : ($rep->efficiency_rate >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                كفاءة: {{ $rep->efficiency_rate }}%
                            </span>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-center text-[11px] bg-white p-2 rounded-xl border border-slate-100">
                            <div><span class="text-slate-400 block">الإنتاج</span><b class="text-slate-800">{{ number_format($rep->units_produced) }} ق</b></div>
                            <div><span class="text-slate-400 block">سرعة السير</span><b class="text-slate-800">{{ $rep->belt_speed_m_min }} م/د</b></div>
                            <div><span class="text-slate-400 block">الهادر</span><b class="{{ $rep->waste_units > 20 ? 'text-rose-600' : 'text-slate-800' }}">{{ $rep->waste_units }} ق</b></div>
                            <div><span class="text-slate-400 block">التوقف</span><b class="{{ $rep->downtime_minutes > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $rep->downtime_minutes }} د</b></div>
                        </div>

                        @if($rep->ai_smart_recommendation)
                            <div class="text-[11px] bg-emerald-50 text-emerald-900 border border-emerald-200 p-2 rounded-xl flex items-start gap-2">
                                <i class="fa-solid fa-lightbulb text-amber-500 text-xs mt-0.5"></i>
                                <div><b class="font-bold">توصية القرار الذكية:</b> {{ $rep->ai_smart_recommendation }}</div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        لا توجد تقارير ساعات مسجلة اليوم حتى الآن.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Factory P&L Chart -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-chart-column text-brand-600"></i>
                <span>مقارنة ربحية المصانع والخطوط الـ 6</span>
            </h2>
            <div class="h-72">
                <canvas id="factoryProfitChart"></canvas>
            </div>
            <div class="text-[11px] text-slate-500 text-center">
                إجمالي التوريدات تشمل هامش الـ 14% للمصانع الداخلية عند توريد المنتجات للمخزن المركزي
            </div>
        </div>

    </div>

    <!-- Recent Sales Orders & End-to-End Cycle Flow -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-spin text-brand-600"></i>
                    <span>متابعة دورة الطلبات عبر الأقسام (مبيعات → تشغيل → مخزن → مشتريات)</span>
                </h2>
                <p class="text-xs text-slate-500">حالة كل طلبية من لحظة اتصال العميل وحتى التسليم النهائي</p>
            </div>
            <a href="{{ route('sales.orders') }}" class="text-xs font-bold text-brand-600 hover:underline">عرض كافة الطلبيات</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم الطلب</th>
                        <th class="py-3 px-4">العميل</th>
                        <th class="py-3 px-4">المنتج والكمية</th>
                        <th class="py-3 px-4">المبلغ الإجمالي</th>
                        <th class="py-3 px-4">مرحلة الدورة الحالية</th>
                        <th class="py-3 px-4">موعد التسليم المعتمد</th>
                        <th class="py-3 px-4">إنذار الخسارة</th>
                        <th class="py-3 px-4 text-center">الإجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $order->order_number }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2 font-bold text-slate-900">
                                    <img src="{{ $order->customer->logo_url }}" class="w-6 h-6 rounded-md object-cover border border-slate-200">
                                    <span>{{ $order->customer->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $order->items->first()?->product->name ?? 'منتجات متعددة' }}
                                <span class="font-bold text-slate-800">({{ number_format($order->items->sum('quantity')) }} ق)</span>
                            </td>
                            <td class="py-3 px-4 font-black text-slate-800">{{ number_format($order->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $order->status_badge['color'] }}">
                                    {{ $order->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $order->confirmed_delivery_date ? $order->confirmed_delivery_date->format('Y-m-d') : 'قيد تحديد المدة' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($order->has_loss_alarm)
                                    <span class="inline-flex items-center gap-1 text-[11px] bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        سعر أقل من التكلفة
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-bold text-xs"><i class="fa-solid fa-check"></i> رابح</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('sales.orders.show', $order->id) }}" class="px-3 py-1 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg font-bold text-xs transition">
                                    تفاصيل الدورة
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('factoryProfitChart').getContext('2d');
        const labels = @json(collect($factoryPerformance)->pluck('name'));
        const revenues = @json(collect($factoryPerformance)->pluck('revenue'));
        const profits = @json(collect($factoryPerformance)->pluck('profit'));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'إجمالي التوريدات (ج.م)',
                        data: revenues,
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    },
                    {
                        label: 'صافي الربح / الهامش (ج.م)',
                        data: profits,
                        backgroundColor: '#008060',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        rtl: true,
                        labels: { font: { family: 'Cairo' } }
                    }
                },
                scales: {
                    x: { ticks: { font: { family: 'Cairo', size: 10 } } },
                    y: { ticks: { font: { family: 'Cairo', size: 10 } } }
                }
            }
        });
    });
</script>
@endpush
