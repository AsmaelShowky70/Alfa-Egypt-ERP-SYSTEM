@extends('layouts.app')

@section('title', 'عرض السند: ' . $voucher->voucher_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $voucher->voucher_number }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                    {{ $voucher->type_arabic }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">سند مخزني رسمي موثق</h1>
            <p class="text-xs text-slate-400">التاريخ: {{ $voucher->voucher_date->format('Y-m-d') }} | أمين المخزن: {{ $voucher->warehouseManager->name ?? 'جورج وليم' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('print.voucher', $voucher->id) }}" target="_blank" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>طباعة السند الورقي الرسمي</span>
            </a>
            <a href="{{ route('warehouse.vouchers') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للسندات
            </a>
        </div>
    </div>

    <!-- Voucher Details -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <div><span class="text-slate-400 block mb-0.5">نوع الإذن</span><b class="text-slate-800">{{ $voucher->type_arabic }}</b></div>
            <div><span class="text-slate-400 block mb-0.5">تاريخ التوثيق</span><b class="text-slate-800">{{ $voucher->voucher_date->format('Y-m-d') }}</b></div>
            <div><span class="text-slate-400 block mb-0.5">المستلم / المسلّم</span><b class="text-slate-800">{{ $voucher->received_or_delivered_by ?? 'غير محدد' }}</b></div>
            <div><span class="text-slate-400 block mb-0.5">حالة الأرشفة</span><b class="text-emerald-700"><i class="fa-solid fa-box-archive"></i> محفوظ ومؤرشف</b></div>
        </div>

        @if($voucher->notes)
            <div class="text-xs text-slate-600 bg-amber-50/50 p-3 rounded-xl border border-amber-200/60">
                <b>ملاحظات وبيان السند:</b> {{ $voucher->notes }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">م</th>
                        <th class="py-3 px-4">اسم الصنف والخامة</th>
                        <th class="py-3 px-4">الوحدة</th>
                        <th class="py-3 px-4">الكمية المسجلة بالسند</th>
                        <th class="py-3 px-4">سعر التكلفة للوحدة</th>
                        <th class="py-3 px-4">إجمالي القيمة المالية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($voucher->items as $idx => $it)
                        <tr>
                            <td class="py-3 px-4 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $it->item_name }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $it->unit }}</td>
                            <td class="py-3 px-4 font-black text-amber-700 text-sm">{{ number_format($it->quantity) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($it->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($it->total_cost, 2) }} ج.م</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-black text-xs border-t-2 border-slate-200">
                    <tr>
                        <td colspan="5" class="py-3.5 px-4 text-left">إجمالي قيمة السند المخزني:</td>
                        <td class="py-3.5 px-4 text-emerald-800 text-sm">{{ number_format($voucher->total_value, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
