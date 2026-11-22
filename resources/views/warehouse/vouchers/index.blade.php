@extends('layouts.app')

@section('title', 'سندات المخزن (استلام وصرف وأرشفة)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-amber-600"></i>
                <span>سندات وأذونات المخزن (أرشفة وطباعة وتحميل)</span>
            </h1>
            <p class="text-xs text-slate-500">إذن استلام من مورد، استلام من مصنع داخلي (+14%)، إذن صرف للتشغيل، أو تسليم لعميل</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إصدار سند مخزن جديد</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Vouchers List -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم السند</th>
                        <th class="py-3 px-4">نوع الإذن</th>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4">القيمة الإجمالية</th>
                        <th class="py-3 px-4">المستلم / المسلّم</th>
                        <th class="py-3 px-4">أمين المخزن</th>
                        <th class="py-3 px-4">الأرشفة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $v->voucher_number }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                    {{ $v->type_arabic }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $v->voucher_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($v->total_value, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $v->received_or_delivered_by ?? 'غير محدد' }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $v->warehouseManager->name ?? 'جورج وليم' }}</td>
                            <td class="py-3 px-4">
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">مؤرشف بالسيرفر</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('warehouse.vouchers.show', $v->id) }}" class="px-3 py-1 bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold rounded-lg transition">
                                        عرض السند
                                    </a>
                                    <a href="{{ route('print.voucher', $v->id) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-slate-800" title="طباعة فورية">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">لا توجد سندات مخزنية مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $vouchers->links() }}
        </div>
    </div>

</div>
@endsection
