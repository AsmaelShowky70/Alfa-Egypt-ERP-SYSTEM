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

    <!-- خطوط الإنتاج المفوضة لتخفيف العبء التشغيلي -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-blue-200/60 pb-3">
            <div class="flex items-center gap-2">
                <span class="p-2 bg-blue-600 text-white rounded-xl text-sm shadow-sm">
                    <i class="fa-solid fa-users-gear"></i>
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-900">متابعة الخطوط المفوضة (لتخفيف العبء عن مدير التشغيل)</h2>
                    <p class="text-xs text-slate-600">تم تفويض متابعة إنتاج الجوانتي والوايبس لمهندسين ومشرفين لمتابعة الإنتاج الفعلي وتقارير الساعات</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-white border border-blue-200 text-blue-700 text-xs font-bold rounded-xl shadow-sm self-start">
                خطين مفوضين نشطين
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- كارت خط الجوانتيات - فادي أشرف -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            عمل إضافي تشغيلي
                        </span>
                        <h3 class="font-black text-sm text-slate-900 mt-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-hand-holding-hand text-amber-600"></i>
                            <span>خط إنتاج الجوانتي (قص MCH-GLV-CUT01 + تغليف PCK01)</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">المسؤول المفوض: <b>فادي أشرف</b> (متابعة الإنتاج والباكت والسرعات)</p>
                    </div>
                    <span class="text-xl font-black text-amber-700 font-mono">{{ number_format($fadiGloveTodayUnits) }} <span class="text-xs font-normal text-slate-400">قطعة اليوم</span></span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">الحالة: <b class="text-emerald-700">يعمل تحت إشراف فادي أشرف</b></span>
                    <a href="{{ route('operations.hourlyReports') }}" class="font-bold text-blue-600 hover:underline">سجل تقارير الساعات ←</a>
                </div>
            </div>

            <!-- كارت خط الوايبس - جرجس غطاس -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            مشرف خط مخصص
                        </span>
                        <h3 class="font-black text-sm text-slate-900 mt-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-soap text-emerald-600"></i>
                            <span>خط إنتاج المناديل المعطرة والوايبس (MCH-WIPES-01)</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">المشرف المفوض: <b>جرجس غطاس</b> (متابعة التدفق، جودة الفويل، ونسب الهدر)</p>
                    </div>
                    <span class="text-xl font-black text-emerald-700 font-mono">{{ number_format($gergesWipesTodayUnits) }} <span class="text-xs font-normal text-slate-400">منديل اليوم</span></span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">الحالة: <b class="text-emerald-700">يعمل تحت إشراف جرجس غطاس</b></span>
                    <a href="{{ route('operations.hourlyReports') }}" class="font-bold text-blue-600 hover:underline">سجل تقارير الساعات ←</a>
                </div>
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
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    @foreach($order->items as $it)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-800">
                                            <span class="font-bold">{{ $it->product?->name }}</span>
                                            <span class="text-brand-700 font-extrabold font-mono">({{ number_format($it->quantity) }} ق)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $order->status_badge['color'] }}">
                            {{ $order->status_badge['label'] }}
                        </span>
                    </div>

                    <!-- Material Availability Checklist across all order items -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/80 text-xs space-y-2">
                        <p class="font-bold text-slate-700">موقف مستلزمات الـ BOM لكافة أصناف الطلبية بالمخزن المركزي:</p>
                        @php
                            $orderRequiredMaterials = [];
                            foreach($order->items as $it) {
                                if ($it->product) {
                                    foreach($it->product->boms as $bom) {
                                        $rmId = $bom->raw_material_id;
                                        $needed = $it->quantity * $bom->quantity_per_unit;
                                        if (!isset($orderRequiredMaterials[$rmId])) {
                                            $orderRequiredMaterials[$rmId] = [
                                                'name' => $bom->rawMaterial->name ?? 'مادة',
                                                'needed' => 0,
                                                'avail' => $bom->rawMaterial->current_stock ?? 0,
                                            ];
                                        }
                                        $orderRequiredMaterials[$rmId]['needed'] += $needed;
                                    }
                                }
                            }
                            $orderHasShortage = false;
                            foreach($orderRequiredMaterials as $mat) {
                                if ($mat['avail'] < $mat['needed']) {
                                    $orderHasShortage = true;
                                    break;
                                }
                            }
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($orderRequiredMaterials as $mat)
                                @php
                                    $isSufficient = $mat['avail'] >= $mat['needed'];
                                @endphp
                                <div class="p-2 rounded-lg border {{ $isSufficient ? 'border-emerald-200 bg-emerald-50/40 text-emerald-900' : 'border-rose-200 bg-rose-50/50 text-rose-900' }}">
                                    <div class="font-bold truncate">{{ $mat['name'] }}</div>
                                    <div class="text-[10px]">{{ number_format($mat['needed']) }} مطلوب / {{ number_format($mat['avail']) }} متاح</div>
                                    <div class="text-[10px] font-extrabold mt-1">
                                        {!! $isSufficient ? '<i class="fa-solid fa-check text-emerald-600"></i> متوفر' : '<i class="fa-solid fa-xmark text-rose-600"></i> عجز ' . number_format($mat['needed'] - $mat['avail']) !!}
                                    </div>
                                </div>
                            @endforeach
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
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">ماكينة التشغيل</label>
                            <select name="machine_id" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="">-- اختياري (تحديد لاحقاً) --</option>
                                @foreach(\App\Models\Machine::all() as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">هل توجد نواقص تستلزم شراء؟</label>
                            <select name="has_missing_materials" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="0" {{ !$orderHasShortage ? 'selected' : '' }}>كافة الخامات متوفرة بالمخزن</option>
                                <option value="1" {{ $orderHasShortage ? 'selected' : '' }}>توجد نواقص يلزم المشتريات توفيرها</option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto self-end pt-1">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
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
