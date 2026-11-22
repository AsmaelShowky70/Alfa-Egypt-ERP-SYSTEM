@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة التشغيل والإنتاج')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-blue-500/20 text-blue-300 border border-blue-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                إدارة التشغيل والمصانع
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا جرجس أنور (مدير التشغيل)</h1>
            <p class="text-blue-100 text-sm max-w-xl">
                تخطيط أوامر التشغيل، فحص الخامات وطلب صرف المستلزمات من المخزن المركزي، تحديد مدة العمل للمبيعات، ومتابعة كفاءة الماكينات وسرعة السير لحظياً.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('operations.hourlyReports') }}" class="px-4 py-2.5 bg-blue-500 hover:bg-blue-400 text-blue-950 font-extrabold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-clock"></i>
                <span>تسجيل تقرير ساعة للماكينة</span>
            </a>
            <a href="{{ route('operations.workOrders') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-gears"></i>
                <span>أوامر الشغل الجارية</span>
            </a>
        </div>
    </div>

    <!-- Operations KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إنتاج اليوم الفعلي</p>
                <h3 class="text-xl font-black text-slate-800">{{ number_format($todayProducedUnits) }} <span class="text-xs font-medium text-slate-500">قطعة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">متوسط كفاءة الماكينات OEE</p>
                <h3 class="text-xl font-black text-emerald-700">{{ number_format($avgEfficiencyToday, 1) }}%</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي دقائق التوقف اليوم</p>
                <h3 class="text-xl font-black text-amber-700">{{ $todayDowntimeMinutes }} <span class="text-xs font-medium text-slate-500">دقيقة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">طلبات مبيعات بانتظار الجدولة</p>
                <h3 class="text-xl font-black text-indigo-700">{{ $pendingOrdersForScheduling->count() }} <span class="text-xs font-medium text-slate-500">طلبية</span></h3>
            </div>
        </div>
    </div>

    <!-- Incoming Orders from Sales Awaiting Operations Scheduling & Material Check -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-amber-500"></i>
                    <span>طلبات مبيعات واردة جديدة (مطلوب فحص الخامات وتحديد مدة العمل)</span>
                </h2>
                <p class="text-xs text-slate-500">يرجى فحص المستلزمات بالمخزن لتحديد موعد التسليم الذي ستبلغه المبيعات للعميل</p>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($pendingOrdersForScheduling as $order)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $order->customer->logo_url }}" class="w-10 h-10 rounded-xl object-contain bg-white p-1 border border-slate-200">
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $order->customer->name }} ({{ $order->order_number }})</h3>
                                <p class="text-xs text-slate-500">المنتج المطلوب: <b class="text-slate-800">{{ $order->items->first()?->product->name }}</b> بمقدار <b class="text-brand-700">{{ number_format($order->items->sum('quantity')) }} قطعة</b></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $order->status_badge['color'] }}">
                            {{ $order->status_badge['label'] }}
                        </span>
                    </div>

                    <!-- Material Availability Checklist -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/80 text-xs">
                        <p class="font-bold text-slate-700 mb-2">موقف مستلزمات الـ BOM بالمخزن المركزي:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @php
                                $firstProd = $order->items->first()?->product;
                            @endphp
                            @if($firstProd)
                                @foreach($firstProd->boms as $bom)
                                    @php
                                        $needed = $order->items->sum('quantity') * $bom->quantity_per_unit;
                                        $avail = $bom->rawMaterial->current_stock ?? 0;
                                        $isSufficient = $avail >= $needed;
                                    @endphp
                                    <div class="p-2 rounded-lg border {{ $isSufficient ? 'border-emerald-200 bg-emerald-50/40 text-emerald-900' : 'border-rose-200 bg-rose-50/50 text-rose-900' }}">
                                        <div class="font-bold truncate">{{ $bom->rawMaterial->name ?? 'مادة' }}</div>
                                        <div class="text-[10px]">{{ number_format($needed) }} مطلوب / {{ number_format($avail) }} متاح</div>
                                        <div class="text-[10px] font-extrabold mt-1">
                                            {!! $isSufficient ? '<i class="fa-solid fa-check text-emerald-600"></i> متوفر' : '<i class="fa-solid fa-xmark text-rose-600"></i> عجز ' . number_format($needed - $avail) !!}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Form to schedule order & set lead time -->
                    <form action="{{ route('operations.schedule', $order->id) }}" method="POST" class="bg-white p-3 rounded-xl border border-slate-200 flex flex-wrap items-center gap-3">
                        @csrf
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">المدة المطلوبة للتشغيل (أيام)</label>
                            <input type="number" name="operations_lead_time_days" value="3" min="1" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        </div>

                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">خط الإنتاج / المصنع</label>
                            <select name="factory_id" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                                @foreach(\App\Models\Factory::all() as $fac)
                                    <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">هل توجد نواقص تستلزم شراء؟</label>
                            <select name="has_missing_materials" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="0">كافة الخامات متوفرة بالمخزن</option>
                                <option value="1">توجد نواقص يلزم المشتريات توفيرها</option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto self-end pt-1">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>إصدار أمر التشغيل وطلب الخامات</span>
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    لا توجد طلبات معلقة من المبيعات حالياً. كافة الطلبات تمت جدولتها.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Active Production Work Orders -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-industry text-blue-600"></i>
                <span>أوامر التشغيل الجارية على خطوط المصنع</span>
            </h2>
            <a href="{{ route('operations.workOrders') }}" class="text-xs font-bold text-blue-600 hover:underline">عرض كل أوامر الشغل</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم أمر الشغل</th>
                        <th class="py-3 px-4">المصنع / الخط</th>
                        <th class="py-3 px-4">المنتج</th>
                        <th class="py-3 px-4">الكمية المخططة</th>
                        <th class="py-3 px-4">المنجز حتى الآن</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($activeWorkOrders as $wo)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">{{ $wo->order_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $wo->factory->name }}</td>
                            <td class="py-3 px-4">{{ $wo->product->name }}</td>
                            <td class="py-3 px-4 font-bold">{{ number_format($wo->planned_quantity) }} ق</td>
                            <td class="py-3 px-4 text-emerald-700 font-black">{{ number_format($wo->produced_quantity) }} ق</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $wo->status_badge['color'] }}">
                                    {{ $wo->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('operations.workOrders.show', $wo->id) }}" class="px-3 py-1 bg-blue-50 text-blue-800 hover:bg-blue-100 rounded-lg font-bold text-xs transition">
                                    متابعة الخط
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
