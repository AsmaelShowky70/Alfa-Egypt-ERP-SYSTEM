@extends('layouts.app')

@section('title', 'محضر جرد: ' . $dailyCheck->check_code)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-400">{{ $dailyCheck->check_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                    {{ $dailyCheck->category_arabic }}
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900 mt-1">محضر مطابقة الجرد الفعلي والدفتري</h1>
            <p class="text-xs text-slate-400">التاريخ: {{ $dailyCheck->check_date->format('Y-m-d') }} | المشرف: {{ $dailyCheck->warehouseManager->name ?? 'جورج وليم' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('print.dailyCheck', $dailyCheck->id) }}" target="_blank" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>طباعة محضر الجرد الرسمي</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع لقائمة الجرد
            </a>
        </div>
    </div>

    <!-- Summary Differences Cards -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-center">
            <span class="text-xs font-bold text-emerald-800 block">إجمالي قيمة الفائض المحسوب</span>
            <span class="text-lg font-black text-emerald-700">+{{ number_format($dailyCheck->total_surplus_value, 2) }} ج.م</span>
        </div>
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-center">
            <span class="text-xs font-bold text-rose-800 block">إجمالي قيمة العجز المحسوب</span>
            <span class="text-lg font-black text-rose-700">-{{ number_format($dailyCheck->total_deficit_value, 2) }} ج.م</span>
        </div>
    </div>

    <!-- Check Items Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">اسم الصنف</th>
                        <th class="py-3 px-4">الرصيد الدفتري</th>
                        <th class="py-3 px-4">الجرد الفعلي</th>
                        <th class="py-3 px-4">فرق الكمية</th>
                        <th class="py-3 px-4">سعر التكلفة</th>
                        <th class="py-3 px-4">الأثر المالي</th>
                        <th class="py-3 px-4">بيان السبب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($dailyCheck->items as $it)
                        <tr>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $it->item_name }}</td>
                            <td class="py-3 px-4 font-bold text-slate-700">{{ number_format($it->system_quantity) }} {{ $it->unit }}</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($it->actual_quantity) }} {{ $it->unit }}</td>
                            <td class="py-3 px-4 font-black {{ $it->difference > 0 ? 'text-emerald-600' : ($it->difference < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                {{ $it->difference > 0 ? '+' : '' }}{{ number_format($it->difference) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-600">{{ number_format($it->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-black {{ $it->financial_impact > 0 ? 'text-emerald-700' : ($it->financial_impact < 0 ? 'text-rose-700' : 'text-slate-400') }}">
                                {{ $it->financial_impact > 0 ? '+' : '' }}{{ number_format($it->financial_impact, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $it->reason ?? 'مطابق' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
