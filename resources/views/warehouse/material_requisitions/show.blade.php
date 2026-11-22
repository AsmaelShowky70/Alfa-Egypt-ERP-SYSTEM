@extends('layouts.app')

@section('title', 'طلب صرف مستلزمات: ' . $materialRequisition->req_number)

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $materialRequisition->req_number }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $materialRequisition->status === 'fully_issued' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $materialRequisition->status === 'fully_issued' ? 'تم الصرف بالكامل' : ($materialRequisition->status === 'partial_issued' ? 'صرف جزئي' : 'بانتظار الإجراء') }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">
                صرف خامات لأمر تشغيل: {{ $materialRequisition->productionOrder->order_number }}
            </h1>
            <p class="text-xs text-slate-500">
                المنتج: <b>{{ $materialRequisition->productionOrder->product->name }}</b> | 
                الكمية: <b>{{ number_format($materialRequisition->productionOrder->planned_quantity) }} قطعة</b> |
                العميل: <b>{{ $materialRequisition->productionOrder->salesOrder->customer->name ?? 'إنتاج عام' }}</b>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($materialRequisition->status !== 'fully_issued')
                <form action="{{ route('warehouse.materialRequisitions.issue', $materialRequisition->id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <span>صرف الخامات المتاحة وإصدار السند</span>
                    </button>
                </form>

                <form action="{{ route('warehouse.materialRequisitions.purchaseMissing', $materialRequisition->id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>إرسال طلب شراء بالنواقص للمشتريات</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Items Table with Available Stock Comparison -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">بنود الخامات المطلوبة وموقف الأرصدة بالمخزن</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">اسم الخامة</th>
                        <th class="py-3 px-4">الكمية المطلوبة</th>
                        <th class="py-3 px-4">الرصيد المتاح بالمخزن</th>
                        <th class="py-3 px-4">الكمية المصروفة</th>
                        <th class="py-3 px-4">العجز / النواقص</th>
                        <th class="py-3 px-4">التكلفة المقدرة</th>
                        <th class="py-3 px-4">الموقف</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($materialRequisition->items as $item)
                        @php
                            $avail = $item->rawMaterial->current_stock;
                            $req = $item->quantity_requested;
                            $isShort = $avail < $req && $item->quantity_issued == 0;
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $item->rawMaterial->name }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($req) }} {{ $item->rawMaterial->unit }}</td>
                            <td class="py-3 px-4 font-bold text-slate-600">{{ number_format($avail) }} {{ $item->rawMaterial->unit }}</td>
                            <td class="py-3 px-4 font-black text-emerald-700">{{ number_format($item->quantity_issued) }}</td>
                            <td class="py-3 px-4 font-black {{ $item->quantity_missing > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($item->quantity_missing) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($req * $item->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                @if($item->quantity_issued >= $req)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">تم الصرف</span>
                                @elseif($item->quantity_missing > 0)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">مطلوب شراؤه</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">متاح للصرف</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
