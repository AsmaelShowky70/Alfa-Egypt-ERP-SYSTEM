@extends('layouts.app')

@section('title', 'بدء جرد يومي للمخزن')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">تسجيل محضر الجرد اليومي للمخزن</h1>
            <p class="text-xs text-slate-500">إدخال الكميات الفعلية بالمخزن لمقارنتها بالرصيد الدفتري واحتساب الفروق المالية تلقائياً</p>
        </div>
        <a href="{{ route('warehouse.dailyChecks') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لمحاضر الجرد
        </a>
    </div>

    <!-- Category Selector Tabs -->
    <div class="flex items-center gap-2 bg-slate-200/70 p-1.5 rounded-2xl w-fit">
        <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'raw_materials']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $category === 'raw_materials' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <i class="fa-solid fa-cubes-stacked mr-1"></i> جرد المواد الخام
        </a>
        <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'finished_goods']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $category === 'finished_goods' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <i class="fa-solid fa-box-open mr-1"></i> جرد المنتج التام (الكوڤير)
        </a>
        <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'spare_parts']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $category === 'spare_parts' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <i class="fa-solid fa-wrench mr-1"></i> جرد قطع الغيار
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('warehouse.dailyChecks.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ الجرد اليومي *</label>
                    <input type="date" name="check_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">ملاحظات لجنة الجرد</label>
                    <input type="text" name="notes" placeholder="تم الجرد بحضور أمين المخزن ومسؤول الجودة..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>
            </div>

            <!-- Items Count Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-black border-b border-slate-200">
                            <th class="py-3 px-4">اسم الصنف</th>
                            <th class="py-3 px-4">الرصيد الدفتري بالنظام</th>
                            <th class="py-3 px-4">الكمية الفعلية بالمخزن *</th>
                            <th class="py-3 px-4">التكلفة للوحدة</th>
                            <th class="py-3 px-4">سبب الفرق إن وجد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $idx => $it)
                            @php
                                $cost = $category === 'finished_goods' ? $it->cost_price : $it->unit_cost;
                                $itemType = $category === 'finished_goods' ? 'product' : 'raw_material';
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-slate-900 text-sm">{{ $it->name }}</div>
                                    <span class="font-mono text-[10px] text-slate-400 font-bold">{{ $it->code }}</span>
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $it->id }}">
                                    <input type="hidden" name="items[{{ $idx }}][item_type]" value="{{ $itemType }}">
                                    <input type="hidden" name="items[{{ $idx }}][system_qty]" value="{{ $it->current_stock }}">
                                    <input type="hidden" name="items[{{ $idx }}][unit_cost]" value="{{ $cost }}">
                                </td>
                                <td class="py-3 px-4 font-black text-slate-700">{{ number_format($it->current_stock) }} {{ $it->unit }}</td>
                                <td class="py-3 px-4">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][actual_qty]" value="{{ $it->current_stock }}" required class="w-32 px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-black text-amber-900 focus:ring-2 focus:ring-amber-600 outline-none">
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-600">{{ number_format($cost, 2) }} ج.م</td>
                                <td class="py-3 px-4">
                                    <input type="text" name="items[{{ $idx }}][reason]" placeholder="فائض/عجز تجارب تشغيل..." class="w-full px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('warehouse.dailyChecks') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-check-double"></i>
                    <span>اعتماد الجرد وتسوية الأرصدة تلقائياً</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
