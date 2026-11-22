@extends('layouts.app')

@section('title', 'طلبات صرف مستلزمات التشغيل')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                <span>طلبات صرف المستلزمات الواردة من إدارة التشغيل</span>
            </h1>
            <p class="text-xs text-slate-500">فحص الخامات وصرف المتاح بإذن صرف رسمي، أو تحويل النواقص لطلب شراء فوري</p>
        </div>
        <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع للمخزن
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم إذن الصرف</th>
                        <th class="py-3 px-4">أمر التشغيل المرتبط</th>
                        <th class="py-3 px-4">المنتج والعميل</th>
                        <th class="py-3 px-4">الكمية المستهدفة</th>
                        <th class="py-3 px-4">تاريخ الطلب</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requisitions as $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $r->req_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $r->productionOrder->order_number }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $r->productionOrder->product->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $r->productionOrder->salesOrder->customer->name ?? 'إنتاج عام' }}</div>
                            </td>
                            <td class="py-3 px-4 font-black">{{ number_format($r->productionOrder->planned_quantity) }} ق</td>
                            <td class="py-3 px-4 text-slate-500">{{ $r->requested_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">
                                @if($r->status === 'fully_issued')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">تم الصرف بالكامل</span>
                                @elseif($r->status === 'partial_issued')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">صرف جزئي (نواقص)</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">بانتظار الصرف</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('warehouse.materialRequisitions.show', $r->id) }}" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-lg transition">
                                    فحص البنود والصرف
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">لا توجد طلبات صرف مستلزمات مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $requisitions->links() }}
        </div>
    </div>

</div>
@endsection
