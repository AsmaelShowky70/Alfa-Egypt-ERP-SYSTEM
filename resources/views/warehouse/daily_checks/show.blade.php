@extends('layouts.app')

@section('title', 'محضر جرد: ' . $dailyCheck->check_code)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">{{ $dailyCheck->check_code }}</span>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                    {{ $dailyCheck->category_arabic }}
                </span>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800">
                    معتمد ومسوى
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2">محضر مطابقة الجرد الفعلي والدفتري</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                تاريخ الجرد: <b class="text-slate-800">{{ $dailyCheck->check_date->format('Y-m-d') }}</b> | المشرف المسؤول: <b class="text-slate-800">{{ $dailyCheck->warehouseManager->name ?? 'جورج وليم' }}</b>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('print.dailyCheck', $dailyCheck->id) }}" target="_blank" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-print text-amber-400"></i>
                <span>طباعة المحضر الرسمي</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                ← رجوع للقائمة
            </a>
        </div>
    </div>

    @if($dailyCheck->notes)
        <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-2.5">
            <i class="fa-solid fa-note-sticky text-amber-600 mt-0.5"></i>
            <div>
                <b class="block font-black mb-0.5">ملاحظات لجنة الجرد:</b>
                <span>{{ $dailyCheck->notes }}</span>
            </div>
        </div>
    @endif

    <!-- Summary Differences Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-emerald-50 border border-emerald-200 p-5 rounded-3xl text-center space-y-1">
            <span class="text-xs font-bold text-emerald-800 block">إجمالي قيمة الفائض المحسوب</span>
            <span class="text-2xl font-black text-emerald-700 font-mono">+{{ number_format($dailyCheck->total_surplus_value, 2) }} ج.م</span>
            <span class="text-[10px] text-emerald-600 block">أصناف زادت عن الرصيد الدفتري</span>
        </div>

        <div class="bg-rose-50 border border-rose-200 p-5 rounded-3xl text-center space-y-1">
            <span class="text-xs font-bold text-rose-800 block">إجمالي قيمة العجز المحسوب</span>
            <span class="text-2xl font-black text-rose-700 font-mono">-{{ number_format($dailyCheck->total_deficit_value, 2) }} ج.م</span>
            <span class="text-[10px] text-rose-600 block">أصناف نقصت عن الرصيد الدفتري</span>
        </div>

        @php
            $netVal = (float)$dailyCheck->total_surplus_value - (float)$dailyCheck->total_deficit_value;
        @endphp
        <div class="bg-white border border-slate-200 p-5 rounded-3xl text-center space-y-1">
            <span class="text-xs font-bold text-slate-500 block">صافي الأثر المالي للتسوية</span>
            <span class="text-2xl font-black font-mono {{ $netVal >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $netVal >= 0 ? '+' : '' }}{{ number_format($netVal, 2) }} ج.م
            </span>
            <span class="text-[10px] text-slate-400 block">{{ $netVal >= 0 ? 'صافي وفر بالمخزن' : 'صافي هادر / خسارة' }}</span>
        </div>
    </div>

    <!-- Check Items Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-black text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list-check text-amber-600"></i>
                <span>تفاصيل بنود الجرد الفعلي والدفتري ({{ $dailyCheck->items->count() }} صنف)</span>
            </h3>
            <span class="text-xs text-slate-400">فروق الأرصدة مسواة آلياً</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">اسم الصنف</th>
                        <th class="py-3 px-4 text-center">الرصيد الدفتري</th>
                        <th class="py-3 px-4 text-center">الجرد الفعلي</th>
                        <th class="py-3 px-4 text-center">فرق الكمية</th>
                        <th class="py-3 px-4 text-left font-mono">سعر التكلفة</th>
                        <th class="py-3 px-4 text-left font-mono">الأثر المالي</th>
                        <th class="py-3 px-4">بيان السبب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($dailyCheck->items as $idx => $it)
                        <tr class="hover:bg-amber-50/20 transition">
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">
                                {{ $it->item_name }}
                                <span class="block text-[10px] font-bold text-slate-400">{{ $it->item_type === 'product' ? 'منتج تام (كوڤير)' : 'مادة خام / مستلزم' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700 font-mono">{{ number_format($it->system_quantity, 2) }} {{ $it->unit }}</td>
                            <td class="py-3 px-4 text-center font-black text-slate-900 font-mono">{{ number_format($it->actual_quantity, 2) }} {{ $it->unit }}</td>
                            <td class="py-3 px-4 text-center font-black font-mono {{ $it->difference > 0 ? 'text-emerald-600' : ($it->difference < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                @if($it->difference > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800">+{{ number_format($it->difference, 2) }} (فائض)</span>
                                @elseif($it->difference < 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-800">{{ number_format($it->difference, 2) }} (عجز)</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600">مطابق</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-left font-bold text-slate-600 font-mono">{{ number_format($it->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-left font-black font-mono {{ $it->financial_impact > 0 ? 'text-emerald-700' : ($it->financial_impact < 0 ? 'text-rose-700' : 'text-slate-400') }}">
                                {{ $it->financial_impact > 0 ? '+' : '' }}{{ number_format($it->financial_impact, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-medium">{{ $it->reason ?? 'مطابق' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
