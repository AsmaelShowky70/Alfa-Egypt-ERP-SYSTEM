@extends('layouts.app')

@section('title', 'أمر شراء: ' . $purchaseOrder->po_number)

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $purchaseOrder->po_number }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $purchaseOrder->status === 'received_in_warehouse' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $purchaseOrder->status === 'received_in_warehouse' ? 'مستلم بالمخزن بالكامل' : 'قيد الشحن والتوريد' }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">
                {{ $purchaseOrder->is_internal_factory ? 'أمر شراء داخلي من خط: ' . ($purchaseOrder->fromFactory->name ?? '') : 'أمر شراء من مورد: ' . ($purchaseOrder->supplier->name ?? '') }}
            </h1>
            <p class="text-xs text-slate-500">
                تاريخ الأمر: <b>{{ $purchaseOrder->order_date->format('Y-m-d') }}</b> | الوجهة: <b>{{ $purchaseOrder->destination_warehouse }}</b>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($purchaseOrder->status !== 'received_in_warehouse')
                <form action="{{ route('purchasing.orders.receive', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('تأكيد استلام هذه الشحنة وإضافتها لأرصدة المخزن المركزي وقيد الـ 14%؟')" class="m-0">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-boxes-packing"></i>
                        <span>تأكيد استلام البضاعة في المخزن وإصدار السند</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('print.purchaseOrder', $purchaseOrder->id) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة أمر الشراء</span>
            </a>
            <a href="{{ route('purchasing.orders') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للأوامر
            </a>
        </div>
    </div>

    <!-- 14% Internal Markup Callout if internal factory -->
    @if($purchaseOrder->is_internal_factory)
        <div class="bg-indigo-50 border border-indigo-200 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center gap-2 font-black text-indigo-900 text-sm">
                <i class="fa-solid fa-circle-check text-indigo-600"></i>
                <span>احتساب هامش الـ 14% تكاليف إنتاج تشغيلية لصالح المصنع الداخلي:</span>
            </div>
            <p class="text-xs text-indigo-800 leading-relaxed">
                قيمة الخامات المباشرة: <b>{{ number_format($purchaseOrder->subtotal, 2) }} ج.م</b> + هامش 14% تشغيلي: <b>{{ number_format($purchaseOrder->internal_markup_amount, 2) }} ج.م</b> = إجمالي التكلفة التي تدخل بها البضاعة للمخزن المركزي: <b>{{ number_format($purchaseOrder->total_amount, 2) }} ج.م</b>.
                تُقيد هذه النسبة كإيراد تشغيلي لحساب هذا الخط لحساب أرباحه المستقلة بدقة.
            </p>
        </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">أصناف وبنود أمر الشراء</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">اسم الصنف / الخامة</th>
                        <th class="py-3 px-4">الكمية المطلوبة</th>
                        <th class="py-3 px-4">الكمية المستلمة فعلياً</th>
                        <th class="py-3 px-4">سعر التكلفة للوحدة</th>
                        <th class="py-3 px-4">المجموع الفرعي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchaseOrder->items as $item)
                        <tr>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $item->item_name }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($item->quantity) }}</td>
                            <td class="py-3 px-4 font-black text-emerald-700">{{ number_format($item->received_quantity) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($item->unit_price, 4) }} ج.م</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($item->subtotal, 2) }} ج.م</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-xs border-t-2 border-slate-200">
                    <tr>
                        <td colspan="4" class="py-2.5 px-4 text-left">المجموع قبل الإضافات:</td>
                        <td class="py-2.5 px-4 font-black text-slate-900">{{ number_format($purchaseOrder->subtotal, 2) }} ج.م</td>
                    </tr>
                    @if($purchaseOrder->internal_markup_amount > 0)
                        <tr class="text-indigo-800">
                            <td colspan="4" class="py-2.5 px-4 text-left">+ هامش إنتاج 14% للمصنع الداخلي:</td>
                            <td class="py-2.5 px-4 font-black">+{{ number_format($purchaseOrder->internal_markup_amount, 2) }} ج.م</td>
                        </tr>
                    @endif
                    @if($purchaseOrder->tax > 0)
                        <tr>
                            <td colspan="4" class="py-2.5 px-4 text-left">+ ضريبة القيمة المضافة:</td>
                            <td class="py-2.5 px-4 font-black text-slate-900">+{{ number_format($purchaseOrder->tax, 2) }} ج.م</td>
                        </tr>
                    @endif
                    <tr class="bg-slate-100 font-black text-sm text-slate-900">
                        <td colspan="4" class="py-3 px-4 text-left">إجمالي قيمة أمر الشراء النهائية:</td>
                        <td class="py-3 px-4 text-purple-900">{{ number_format($purchaseOrder->total_amount, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
