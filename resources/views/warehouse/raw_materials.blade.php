@extends('layouts.app')

@section('title', 'المواد الخام ومستلزمات الكوڤير')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cubes-stacked text-amber-600"></i>
                <span>المواد الخام ومستلزمات إنتاج الكوڤير (أسعار واقعية دقيقة)</span>
            </h1>
            <p class="text-xs text-slate-500">معلقة إنجليزي 90 قرش، منديل بيور 24 قرش، خلة صيني 6 قروش، منديل معطر 28 قرش، غلاف شفاف 18 قرش</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>إذن استلام خامات للمخزن</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">كود الخامة</th>
                        <th class="py-3 px-4">اسم الخامة والمستلزم</th>
                        <th class="py-3 px-4">الوحدة</th>
                        <th class="py-3 px-4">التكلفة الفعلية للقطعة</th>
                        <th class="py-3 px-4">الرصيد المتاح بالمخزن</th>
                        <th class="py-3 px-4">إجمالي القيمة المخزنية</th>
                        <th class="py-3 px-4">حد الطلب الآمن</th>
                        <th class="py-3 px-4">المورد / خط الإنتاج المصدر</th>
                        <th class="py-3 px-4">الموقع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($materials as $m)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $m->code }}</td>
                            <td class="py-3 px-4 font-black text-slate-900 text-sm">{{ $m->name }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $m->unit }}</td>
                            <td class="py-3 px-4 font-black text-brand-700 text-sm">
                                {{ number_format($m->unit_cost, 2) }} ج.م
                                @if($m->unit_cost < 1)
                                    <span class="text-[10px] text-slate-400 font-normal">({{ round($m->unit_cost * 100) }} قرش)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-extrabold {{ $m->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($m->current_stock) }} {{ $m->unit }}
                                @if($m->isLowStock())
                                    <span class="inline-block text-[10px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded mr-1">عجز</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-black text-slate-800">{{ number_format($m->current_stock * $m->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-slate-500">{{ number_format($m->reorder_level) }} {{ $m->unit }}</td>
                            <td class="py-3 px-4">
                                @if($m->sourceFactory)
                                    <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                        {{ $m->sourceFactory->name }} (+14%)
                                    </span>
                                @elseif($m->supplier)
                                    <span class="text-[11px] font-medium text-slate-700">{{ $m->supplier->name }}</span>
                                @else
                                    <span class="text-slate-400">مورد خارجي</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-medium">{{ $m->warehouse_location ?? 'عنبر عام' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $materials->links() }}
        </div>
    </div>

</div>
@endsection
