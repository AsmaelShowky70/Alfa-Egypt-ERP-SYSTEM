@extends('layouts.app')

@section('title', 'أمر تشغيل: ' . $productionOrder->order_number)

@section('content')
<div class="space-y-6">

    <!-- Top Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $productionOrder->order_number }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $productionOrder->status_badge['color'] }}">
                    {{ $productionOrder->status_badge['label'] }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">
                {{ $productionOrder->product->name }} — {{ $productionOrder->factory->name }}
            </h1>
            <p class="text-xs text-slate-500">
                العميل: <b>{{ $productionOrder->salesOrder->customer->name ?? 'إنتاج للمخزن المركزي' }}</b> | الماكينة: <b>{{ $productionOrder->machine->name ?? 'غير محددة' }}</b>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('print.workOrder', $productionOrder->id) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة أمر الشغل والمقادير</span>
            </a>
            <button onclick="document.getElementById('statusModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تحديث حالة الإنتاج والكمية</span>
            </button>
        </div>
    </div>

    <!-- Production Progress & Metrics -->
    @php
        $planned = $productionOrder->planned_quantity;
        $produced = $productionOrder->produced_quantity;
        $pct = $planned > 0 ? min(100, round(($produced / $planned) * 100)) : 0;
    @endphp
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
            <span>تقدم الإنتاج الفعلي: <b class="text-blue-600">{{ number_format($produced) }} ق</b> من إجمالي <b class="text-slate-900">{{ number_format($planned) }} ق</b></span>
            <span class="text-blue-700 font-black text-sm">{{ $pct }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs pt-2">
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block mb-0.5 text-[11px]">الهادر المسجل</span>
                <b class="text-rose-700 font-black text-base">{{ number_format($productionOrder->defective_quantity) }} قطعة</b>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block mb-0.5 text-[11px]">الساعات التقديرية</span>
                <b class="text-slate-800 font-black text-base">{{ $productionOrder->estimated_hours }} ساعة</b>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block mb-0.5 text-[11px]">تاريخ البدء المجدول</span>
                <b class="text-slate-800 font-bold text-xs">{{ $productionOrder->scheduled_start_date ? $productionOrder->scheduled_start_date->format('Y-m-d') : 'فوري' }}</b>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block mb-0.5 text-[11px]">الموعد المستهدف للإنهاء</span>
                <b class="text-slate-800 font-bold text-xs">{{ $productionOrder->scheduled_end_date ? $productionOrder->scheduled_end_date->format('Y-m-d') : 'غير محدد' }}</b>
            </div>
        </div>
    </div>

    <!-- Material Requisition Status -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
                <span>مستلزمات الإنتاج وخامات الـ BOM المطلوبة من المخزن</span>
            </h2>
            @if($productionOrder->materialRequisitions->first())
                <a href="{{ route('warehouse.materialRequisitions.show', $productionOrder->materialRequisitions->first()->id) }}" class="text-xs font-bold text-brand-600 hover:underline">
                    عرض إذن الصرف المخزني
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">الخامة / المكون</th>
                        <th class="py-3 px-4">الكمية لكل قطعة</th>
                        <th class="py-3 px-4">إجمالي المطلوب لأمر الشغل</th>
                        <th class="py-3 px-4">المصروف من المخزن</th>
                        <th class="py-3 px-4">موقف الصرف</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($productionOrder->product->boms as $bom)
                        @php
                            $needed = $productionOrder->planned_quantity * $bom->quantity_per_unit;
                            $reqItem = $productionOrder->materialRequisitions->first()?->items->where('raw_material_id', $bom->raw_material_id)->first();
                            $issued = $reqItem ? (float)$reqItem->quantity_issued : 0;
                            $isFullyIssued = $issued >= $needed;
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $bom->rawMaterial->name ?? 'مكون' }}</td>
                            <td class="py-3 px-4">{{ $bom->quantity_per_unit }} {{ $bom->rawMaterial->unit ?? 'قطعة' }}</td>
                            <td class="py-3 px-4 font-black">{{ number_format($needed) }} {{ $bom->rawMaterial->unit ?? 'قطعة' }}</td>
                            <td class="py-3 px-4 text-emerald-700 font-bold">{{ number_format($issued) }}</td>
                            <td class="py-3 px-4">
                                @if($isFullyIssued)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">تم الصرف بالكامل</span>
                                @elseif($issued > 0)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">صرف جزئي</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">بانتظار الصرف</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal to update work order status & produced qty -->
    <div id="statusModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900">تحديث تقدم أمر التشغيل</h3>
                <button onclick="document.getElementById('statusModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('operations.workOrders.status', $productionOrder->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">حالة أمر التشغيل</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-blue-600 outline-none">
                        <option value="ready" {{ $productionOrder->status === 'ready' ? 'selected' : '' }}>جاهز للبدء</option>
                        <option value="in_progress" {{ $productionOrder->status === 'in_progress' ? 'selected' : '' }}>جاري التشغيل على الخط</option>
                        <option value="completed" {{ $productionOrder->status === 'completed' ? 'selected' : '' }}>مكتمل وتوريد للمخزن</option>
                        <option value="paused" {{ $productionOrder->status === 'paused' ? 'selected' : '' }}>متوقف مؤقتاً</option>
                        <option value="cancelled" {{ $productionOrder->status === 'cancelled' ? 'selected' : '' }}>إلغاء</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الكمية المنتجة الفعلية الإجمالية (قطعة)</label>
                    <input type="number" name="produced_quantity" value="{{ $productionOrder->produced_quantity }}" min="0" step="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-blue-700 focus:bg-white focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الكمية التالفة / الهادر (قطعة)</label>
                    <input type="number" name="defective_quantity" value="{{ $productionOrder->defective_quantity }}" min="0" step="1" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-rose-700 focus:bg-white focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('statusModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow">حفظ التحديث</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
