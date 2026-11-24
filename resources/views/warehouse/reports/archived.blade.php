@extends('layouts.app')

@section('title', 'أرشيف تقارير المخازن المعتمدة - الفا ايجبت')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 text-2xl font-black">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800">أرشيف تقارير المخازن المعتمدة</h1>
                <p class="text-xs text-slate-500 mt-1">
                    السجل الرقمي الموثق لكافة تقارير استلام وصرف الخامات والمنتج التام مع التوقيعات الإلكترونية المزدوجة
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.reports') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md hover:bg-amber-700 transition">
                <i class="fa-solid fa-chart-pie"></i>
                <span>لوحة تقارير المخازن والحركات</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('warehouse.reports.archived') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="ابحث برقم التقرير (WH-REP-...) أو العنوان أو اسم الجهة أو المستلم..." 
                       class="w-full text-xs p-2.5 rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div class="w-48">
                <select name="report_type" class="w-full text-xs p-2.5 rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 font-bold">
                    <option value="all">كافة أنواع التقارير</option>
                    <option value="raw_receipt_by_item" {{ $type === 'raw_receipt_by_item' ? 'selected' : '' }}>استلام صنف خامات</option>
                    <option value="raw_issue_by_item" {{ $type === 'raw_issue_by_item' ? 'selected' : '' }}>صرف صنف خامات</option>
                    <option value="finished_receipt_production" {{ $type === 'finished_receipt_production' ? 'selected' : '' }}>استلام منتج تام</option>
                    <option value="finished_delivery_customer" {{ $type === 'finished_delivery_customer' ? 'selected' : '' }}>تسليم لعميل</option>
                    <option value="raw_receipt_supplier" {{ $type === 'raw_receipt_supplier' ? 'selected' : '' }}>استلام من مورد</option>
                    <option value="raw_receipt_factories" {{ $type === 'raw_receipt_factories' ? 'selected' : '' }}>استلام من المصانع</option>
                    <option value="raw_issue_factories" {{ $type === 'raw_issue_factories' ? 'selected' : '' }}>تسليم للمصانع</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                <i class="fa-solid fa-magnifying-glass ml-1"></i>
                بحث
            </button>

            @if($search || ($type && $type !== 'all'))
            <a href="{{ route('warehouse.reports.archived') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200">
                إلغاء التصفية
            </a>
            @endif
        </form>
    </div>

    <!-- Archived Reports Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-3 px-4 text-center">#</th>
                        <th class="py-3 px-4">رقم التقرير المؤرشف</th>
                        <th class="py-3 px-4">عنوان التقرير ونوعه</th>
                        <th class="py-3 px-4">الفترة الزمنية</th>
                        <th class="py-3 px-4">الجهة / المستلم</th>
                        <th class="py-3 px-4 text-center">إجمالي الكمية</th>
                        <th class="py-3 px-4 text-left font-mono">القيمة الإجمالية</th>
                        <th class="py-3 px-4 text-center">حالة التوقيعات</th>
                        <th class="py-3 px-4">تاريخ الأرشفة</th>
                        <th class="py-3 px-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($archivedReports as $idx => $rep)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $archivedReports->firstItem() + $idx }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-amber-700">
                            <a href="{{ route('warehouse.reports.showArchived', $rep->id) }}" class="hover:underline flex items-center gap-1.5">
                                <i class="fa-solid fa-file-contract text-slate-400"></i>
                                <span>{{ $rep->report_number }}</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $rep->title }}
                            <span class="block text-[10px] text-slate-400 mt-0.5">
                                {{ $rep->items_count }} بنود مسجلة
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                            من {{ $rep->from_date?->format('Y-m-d') ?? '-' }} إلى {{ $rep->to_date?->format('Y-m-d') ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-700">
                            {{ $rep->party_name ?? 'عام' }}
                            @if($rep->recipient_name)
                                <span class="block text-[10px] text-slate-500 font-normal">المستلم: {{ $rep->recipient_name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">
                            {{ number_format($rep->total_quantity, 2) }}
                        </td>
                        <td class="py-3 px-4 text-left font-mono font-bold text-emerald-700">
                            {{ number_format($rep->total_value, 2) }} ج.م
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($rep->is_signed_by_recipient && $rep->warehouse_manager_signature)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-check-double"></i>
                                    <span>موقع ومعتمد بالكامل</span>
                                </span>
                            @elseif($rep->warehouse_manager_signature)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-signature"></i>
                                    <span>موقع من أمين المخزن</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 inline-flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>مؤرشف رسمياً</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                            {{ $rep->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('warehouse.reports.showArchived', $rep->id) }}" class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" title="معاينة التقرير والشهادة">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('warehouse.reports.showArchived', $rep->id) }}?print=1" class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100" title="إعادة الطباعة">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-archive text-3xl mb-2 block"></i>
                            <span>لا توجد تقارير مخزنية مؤرشفة حتى الآن.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($archivedReports->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $archivedReports->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
