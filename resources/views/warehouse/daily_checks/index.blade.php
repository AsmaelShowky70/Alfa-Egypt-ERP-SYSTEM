@extends('layouts.app')

@section('title', 'الجرد اليومي للمخزن')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-amber-600"></i>
                <span>محاضر الجرد اليومي للمخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500">جرد يومي مفصل للمواد الخام، المنتج التام، وقطع الغيار لمطابقة الفعلي مع الدفتري</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.dailyChecks.create') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>بدء جرد يومي جديد</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Daily Checks List -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">كود محضر الجرد</th>
                        <th class="py-3 px-4">تاريخ الجرد</th>
                        <th class="py-3 px-4">الفئة التي تم جردها</th>
                        <th class="py-3 px-4">أمين المخزن المسجل</th>
                        <th class="py-3 px-4">قيمة الفائض</th>
                        <th class="py-3 px-4">قيمة العجز</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($checks as $chk)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $chk->check_code }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $chk->check_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $chk->category_arabic }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $chk->warehouseManager->name ?? 'جورج وليم' }}</td>
                            <td class="py-3 px-4 text-emerald-700 font-bold">+{{ number_format($chk->total_surplus_value, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-rose-700 font-bold">-{{ number_format($chk->total_deficit_value, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    معتمد ومطابق
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('warehouse.dailyChecks.show', $chk->id) }}" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-xs rounded-lg transition">
                                        عرض الفروق
                                    </a>
                                    <a href="{{ route('print.dailyCheck', $chk->id) }}" target="_blank" class="p-1 text-slate-400 hover:text-slate-700" title="طباعة محضر الجرد">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">لا توجد محاضر جرد يومي سابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $checks->links() }}
        </div>
    </div>

</div>
@endsection
