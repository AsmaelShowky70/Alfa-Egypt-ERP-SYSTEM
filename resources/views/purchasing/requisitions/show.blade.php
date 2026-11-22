@extends('layouts.app')

@section('title', 'طلب نواقص: ' . $purchaseRequisition->pr_number)

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $purchaseRequisition->pr_number }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $purchaseRequisition->status === 'pending_procurement' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $purchaseRequisition->status === 'pending_procurement' ? 'بانتظار تحديد مدة التوريد' : 'تم الرد بالمدة' }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">
                طلب نواقص خامات لصالح: {{ $purchaseRequisition->salesOrder->customer->name ?? 'المخزن المركزي العام' }}
            </h1>
            <p class="text-xs text-slate-500">
                طلب بواسطة أمين المخزن: <b>{{ $purchaseRequisition->warehouseManager->name ?? 'جورج وليم' }}</b> | تاريخ الإحالة: <b>{{ $purchaseRequisition->created_at->format('Y-m-d') }}</b>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchasing.orders.create') }}?requisition_id={{ $purchaseRequisition->id }}" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-contract"></i>
                <span>إصدار أمر الشراء فوراً</span>
            </a>
            <a href="{{ route('purchasing.requisitions') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للقائمة
            </a>
        </div>
    </div>

    <!-- Lead Time Response Box (يؤثر على موعد تسليم العميل النهائي) -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-3xl p-6 shadow-sm space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-purple-950 flex items-center gap-2">
                <i class="fa-solid fa-clock text-purple-700"></i>
                <span>تحديد مدة التوريد وانعكاسها على موعد التسليم النهائي للعميل</span>
            </h2>
            <p class="text-xs text-purple-700">عند حفظ مدة التوريد هنا، سيقوم النظام تلقائياً بتحديث تاريخ التسليم النهائي وإتاحته للمبيعات للرد على العميل</p>
        </div>

        <form action="{{ route('purchasing.requisitions.leadTime', $purchaseRequisition->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-white p-4 rounded-2xl border border-purple-200">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">المدة اللازمة لتوفير الخامات (أيام) *</label>
                <input type="number" name="procurement_lead_time_days" value="{{ $purchaseRequisition->procurement_lead_time_days ?: 3 }}" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-purple-800 focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">التاريخ المتوقع لوصول الشحنة للمخزن *</label>
                <input type="date" name="estimated_arrival_date" value="{{ $purchaseRequisition->estimated_arrival_date ? $purchaseRequisition->estimated_arrival_date->format('Y-m-d') : date('Y-m-d', strtotime('+3 days')) }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>حفظ المدة وإبلاغ التشغيل والمبيعات</span>
                </button>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات وإفادة المشتريات *</label>
                <input type="text" name="procurement_reply_notes" value="{{ $purchaseRequisition->procurement_reply_notes ?? 'تم الاتصال بالمورد وتأكيد شحن الخامات للمخزن المركزي خلال المدة المذكورة أعلاه.' }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
            </div>
        </form>
    </div>

    <!-- Missing Items List -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">الخامات والمستلزمات المطلوب شراؤها وتوريدها</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">اسم الخامة</th>
                        <th class="py-3 px-4">الكمية المطلوبة</th>
                        <th class="py-3 px-4">الرصيد لحظة الطلب</th>
                        <th class="py-3 px-4">التكلفة التقديرية</th>
                        <th class="py-3 px-4">المورد المقترح</th>
                        <th class="py-3 px-4">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchaseRequisition->items as $item)
                        <tr>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $item->rawMaterial->name }}</td>
                            <td class="py-3 px-4 font-black text-purple-700 text-sm">{{ number_format($item->needed_quantity) }} {{ $item->rawMaterial->unit }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ number_format($item->current_stock_at_request) }} {{ $item->rawMaterial->unit }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($item->estimated_unit_price, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-slate-700">{{ $item->supplier->name ?? 'مورد خارجي معتمد' }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
