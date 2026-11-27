@extends('layouts.app')

@section('title', 'محاضر الجرد اليومي للمخزن المركزي')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 text-2xl font-black">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900">سجل محاضر الجرد اليومي للمخزن المركزي</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    متابعة دورية لجرد المواد الخام، المنتجات التامة، وقطع الغيار واحتساب التسويات الدفترية والفروق المالية
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.dailyChecks.create') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-amber-400"></i>
                <span>بدء جرد يومي جديد</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                لوحة المخازن
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-400 mb-1">إجمالي محاضر الجرد</span>
                <span class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalChecksCount) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">محضر معتمد ومسجل</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-signature"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-emerald-700 mb-1">إجمالي قيمة الفائض</span>
                <span class="text-2xl font-black text-emerald-600 font-mono">+{{ number_format($totalSurplusSum, 2) }}</span>
                <span class="text-[11px] text-emerald-600 block mt-0.5">جنيه مصري (ج.م)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-rose-700 mb-1">إجمالي قيمة العجز</span>
                <span class="text-2xl font-black text-rose-600 font-mono">-{{ number_format($totalDeficitSum, 2) }}</span>
                <span class="text-[11px] text-rose-600 block mt-0.5">جنيه مصري (ج.م)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-trend-down"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-400 mb-1">صافي الأثر المالي التراكمي</span>
                <span class="text-2xl font-black font-mono {{ $netVarianceSum >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $netVarianceSum >= 0 ? '+' : '' }}{{ number_format($netVarianceSum, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 block mt-0.5">{{ $netVarianceSum >= 0 ? 'فائض مخزني عام' : 'عجز مخزني عام' }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $netVarianceSum >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-xl">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('warehouse.dailyChecks') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">فئة الجرد</label>
                <select name="category" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 focus:border-amber-500">
                    <option value="">-- كافة الفئات --</option>
                    <option value="raw_materials" {{ request('category') === 'raw_materials' ? 'selected' : '' }}>المواد الخام ومستلزمات الإنتاج</option>
                    <option value="finished_goods" {{ request('category') === 'finished_goods' ? 'selected' : '' }}>المنتجات التامة والكوڤير</option>
                    <option value="spare_parts" {{ request('category') === 'spare_parts' ? 'selected' : '' }}>قطع الغيار والعدد</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter"></i>
                    <span>تطبيق الفلترة</span>
                </button>
                <a href="{{ route('warehouse.dailyChecks') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Daily Checks Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-table-list text-amber-600"></i>
                <span>قائمة محاضر الجرد المعتمدة بالمخزن ({{ $checks->total() }} محضر)</span>
            </h3>
            <span class="text-xs text-slate-400">التحديث اللحظي مفعل</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                        <th class="py-3 px-4">كود محضر الجرد</th>
                        <th class="py-3 px-4">تاريخ الجرد</th>
                        <th class="py-3 px-4">فئة الجرد</th>
                        <th class="py-3 px-4">أمين المخزن المسجل</th>
                        <th class="py-3 px-4 text-center">عدد البنود</th>
                        <th class="py-3 px-4 text-left font-mono">قيمة الفائض</th>
                        <th class="py-3 px-4 text-left font-mono">قيمة العجز</th>
                        <th class="py-3 px-4 text-left font-mono">صافي الأثر</th>
                        <th class="py-3 px-4 text-center">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($checks as $chk)
                        @php
                            $net = (float)$chk->total_surplus_value - (float)$chk->total_deficit_value;
                        @endphp
                        <tr class="hover:bg-amber-50/20 transition">
                            <td class="py-3 px-4 font-mono font-black text-amber-700">
                                <a href="{{ route('warehouse.dailyChecks.show', $chk->id) }}" class="hover:underline">
                                    {{ $chk->check_code }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-700">{{ $chk->check_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-800">
                                    {{ $chk->category_arabic }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-bold">{{ $chk->warehouseManager->name ?? 'جورج وليم' }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">{{ $chk->items->count() }}</td>
                            <td class="py-3 px-4 text-left font-mono font-bold text-emerald-700">
                                +{{ number_format($chk->total_surplus_value, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-left font-mono font-bold text-rose-700">
                                -{{ number_format($chk->total_deficit_value, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-left font-mono font-black {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center justify-center gap-1 mx-auto w-fit">
                                    <i class="fa-solid fa-check text-[9px]"></i>
                                    <span>معتمد ومسوى</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('warehouse.dailyChecks.show', $chk->id) }}" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-xs rounded-lg transition flex items-center gap-1 shadow-xs">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        <span>عرض الفروق</span>
                                    </a>
                                    <a href="{{ route('print.dailyCheck', $chk->id) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition" title="طباعة محضر الجرد">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-clipboard-check text-3xl mb-2 text-slate-300 block"></i>
                                <p class="font-bold text-slate-600">لا توجد محاضر جرد مسجلة مطابقة لمعايير البحث.</p>
                                <a href="{{ route('warehouse.dailyChecks.create') }}" class="mt-2 text-amber-600 font-bold underline inline-block">ابدأ جرد يومي جديد الآن</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($checks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $checks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
