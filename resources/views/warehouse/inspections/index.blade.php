@extends('layouts.app')

@section('title', 'محاضر فحص واستلام البضاعة والخامات')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                <span>محاضر فحص واستلام المخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">فحص الجودة والكميات عند الاستلام من الموردين أو المصانع الداخلية، وتحديد المقبول والمرفوض مع إمكانية الإضافة التلقائية للمخزن والطباعة</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.inspections.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تحرير محضر فحص واستلام جديد</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                ← لوحة المخزن
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                        <th class="py-3 px-4">رقم المحضر</th>
                        <th class="py-3 px-4">تاريخ الفحص</th>
                        <th class="py-3 px-4">الجهة الموردة / المصدر</th>
                        <th class="py-3 px-4">رقم الفاتورة / أمر الشراء</th>
                        <th class="py-3 px-4">مسؤول الفحص</th>
                        <th class="py-3 px-4 text-center">عدد البنود</th>
                        <th class="py-3 px-4 text-center">نتيجة الفحص</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inspections as $insp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-black text-slate-900">{{ $insp->inspection_number }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $insp->inspection_date?->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $insp->source_display_name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $insp->supplier_invoice_number ?? $insp->purchaseOrder?->po_number ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $insp->inspector_name }}</td>
                            <td class="py-3 px-4 text-center font-bold">{{ $insp->items->count() }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $insp->status_badge_class }}">
                                    {{ $insp->status_arabic }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('warehouse.inspections.show', $insp->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition">
                                        عرض
                                    </a>
                                    <a href="{{ route('print.receivingInspection', $insp->id) }}" target="_blank" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-print"></i>
                                        <span>طباعة</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 space-y-2">
                                <i class="fa-solid fa-clipboard-check text-3xl text-slate-300 block"></i>
                                <span class="block font-bold text-slate-500">لا توجد محاضر فحص واستلام مسجلة حتى الآن.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
