@extends('layouts.app')

@section('title', 'قطع غيار الماكينات والسيور')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-wrench text-blue-600"></i>
                <span>مخزن قطع الغيار ومستلزمات صيانة الماكينات</span>
            </h1>
            <p class="text-xs text-slate-500">سيور ناقلة، سخانات لحام، سكاكين تقطيع كوفير، فوتوسيل وحساسات</p>
        </div>
        <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع للمخزن
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">كود الصنف</th>
                        <th class="py-3 px-4">اسم قطعة الغيار</th>
                        <th class="py-3 px-4">الوحدة</th>
                        <th class="py-3 px-4">التكلفة للوحدة</th>
                        <th class="py-3 px-4">الرصيد المتاح</th>
                        <th class="py-3 px-4">حد الأمان</th>
                        <th class="py-3 px-4">الموقع بالمستودع</th>
                        <th class="py-3 px-4">المورد المعتمد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($spareParts as $sp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $sp->code }}</td>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $sp->name }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $sp->unit }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($sp->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-extrabold {{ $sp->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($sp->current_stock) }} {{ $sp->unit }}
                                @if($sp->isLowStock())
                                    <span class="inline-block text-[10px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded mr-1">منخفض</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ number_format($sp->reorder_level) }} {{ $sp->unit }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $sp->warehouse_location ?? 'دولاب قطع الغيار' }}</td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $sp->supplier->name ?? 'مورد قطع غيار' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">لا توجد قطع غيار مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
