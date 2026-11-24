@extends('layouts.app')

@section('title', 'إدارة التشغيل وتخطيط الإنتاج')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-600"></i>
                <span>تخطيط أوامر التشغيل وفحص الخامات</span>
            </h1>
            <p class="text-xs text-slate-500">استلام طلبات المبيعات، فحص توفر المستلزمات بالمخزن، وتحديد مدة التصنيع</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('operations.hourlyReports') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-clock"></i>
                <span>تسجيل تقرير ساعة للماكينة</span>
            </a>
            <a href="{{ route('operations.workOrders') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                أوامر الشغل
            </a>
        </div>
    </div>

    <!-- Active Factories Capacity Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($factories as $f)
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $f->type === 'internal' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $f->type === 'internal' ? 'خط داخلي' : 'تصنيع عند الغير' }}
                        </span>
                        <h3 class="font-bold text-sm text-slate-900 mt-1">{{ $f->name }}</h3>
                    </div>
                    <span class="font-mono text-xs font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">
                        {{ $f->code }}
                    </span>
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div class="flex items-center justify-between"><span class="text-slate-400">الماكينات التابعة:</span> <b>{{ $f->machines_count }} ماكينات</b></div>
                    <div class="flex items-center justify-between"><span class="text-slate-400">أوامر الشغل:</span> <b class="text-blue-700">{{ $f->production_orders_count }} أمر</b></div>
                    <div class="flex items-center justify-between"><span class="text-slate-400">تكلفة التشغيل/ساعة:</span> <b>{{ number_format($f->operating_cost_per_hour, 2) }} ج.م</b></div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">المشرف: {{ $f->supervisor_name ?? 'غير محدد' }}</span>
                    <a href="{{ route('factories.show', $f->id) }}" class="font-bold text-blue-600 hover:underline">لوحة الخط →</a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pending Orders for Scheduling -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-list-check text-blue-600"></i>
            <span>الطلبات الواردة بانتظار جدولة التشغيل وصرف الخامات ({{ $pendingSalesOrders->count() }})</span>
        </h2>

        <div class="space-y-4">
            @forelse($pendingSalesOrders as $order)
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $order->customer->logo_url }}" class="w-12 h-12 rounded-xl object-contain bg-white p-1 border border-slate-200 shadow-sm">
                            <div>
                                <h3 class="font-black text-sm text-slate-900">{{ $order->customer->name }} ({{ $order->order_number }})</h3>
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
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->status_badge['color'] }}">
                            {{ $order->status_badge['label'] }}
                        </span>
                    </div>

                    <!-- Material stock status -->
                    <div class="bg-white p-4 rounded-xl border border-slate-200/90 text-xs space-y-2">
                        <p class="font-bold text-slate-700">موقف خامات كافة أصناف الطلبية (BOM) في المخزن المركزي:</p>
                        @php
                            $orderRequiredMaterials = [];
                            foreach($order->items as $it) {
                                if ($it->product) {
                                    foreach($it->product->boms as $bom) {
                                        $rmId = $bom->raw_material_id;
                                        $needed = $it->quantity * $bom->quantity_per_unit;
                                        if (!isset($orderRequiredMaterials[$rmId])) {
                                            $orderRequiredMaterials[$rmId] = [
                                                'name' => $bom->rawMaterial->name ?? 'خامة',
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
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            @foreach($orderRequiredMaterials as $mId => $mat)
                                @php
                                    $isSufficient = $mat['avail'] >= $mat['needed'];
                                @endphp
                                <div class="p-2.5 rounded-xl border {{ $isSufficient ? 'border-emerald-200 bg-emerald-50/50 text-emerald-900' : 'border-rose-200 bg-rose-50/60 text-rose-900' }}">
                                    <div class="font-extrabold truncate">{{ $mat['name'] }}</div>
                                    <div class="text-[11px] mt-0.5">{{ number_format($mat['needed']) }} مطلوب / {{ number_format($mat['avail']) }} بالمخزن</div>
                                    <div class="text-[11px] font-black mt-1">
                                        {!! $isSufficient ? '<i class="fa-solid fa-check text-emerald-600"></i> رصيد كافٍ' : '<i class="fa-solid fa-xmark text-rose-600"></i> عجز ' . number_format($mat['needed'] - $mat['avail']) . ' ق' !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Scheduling Form -->
                    <form action="{{ route('operations.schedule', $order->id) }}" method="POST" class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap items-center gap-4">
                        @csrf
                        <div class="flex-1 min-w-[130px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">مدة العمل المطلوبة (أيام)</label>
                            <input type="number" name="operations_lead_time_days" value="3" min="1" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        </div>

                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">المصنع / الخط المنفذ</label>
                            <select name="factory_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                                @foreach($factories as $fac)
                                    <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">ماكينة التشغيل</label>
                            <select name="machine_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="">-- اختياري (تحديد لاحقاً) --</option>
                                @foreach($machines as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[170px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">موقف الخامات (فحص آلي بالمخزن)</label>
                            @if($orderHasShortage)
                                <div class="px-3 py-2 bg-rose-50 border border-rose-200 rounded-xl text-[11px] font-black text-rose-700 flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                    <span>توجد نواقص (إرسال طلب شراء للمشتريات)</span>
                                </div>
                            @else
                                <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] font-black text-emerald-700 flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>الخامات متوفرة بالكامل بالمخزن</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">تعليمات التشغيل (اختياري)</label>
                            <input type="text" name="notes" placeholder="ملاحظات تشغيل إن وجدت..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                        </div>

                        <div class="w-full sm:w-auto self-end pt-1">
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>إصدار أمر التشغيل وطلب الخامات</span>
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    لا توجد طلبيات مبيعات معلقة تتطلب جدولة حالياً.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
