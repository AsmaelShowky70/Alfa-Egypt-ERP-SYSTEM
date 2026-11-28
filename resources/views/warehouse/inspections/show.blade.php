@extends('layouts.app')

@section('title', 'تفاصيل محضر الفحص والاستلام ' . $inspection->inspection_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $inspection->status_badge_class }}">
                    {{ $inspection->status_arabic }}
                </span>
                <span class="font-mono text-sm font-black text-slate-500">{{ $inspection->inspection_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">محضر فحص واستلام بضاعة</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('print.receivingInspection', $inspection->id) }}" target="_blank" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>طباعة المحضر الرسمي (A4)</span>
            </a>
            <a href="{{ route('warehouse.inspections.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                ← قائمة المحاضر
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-slate-400 block font-bold">تاريخ الفحص</span>
            <strong class="text-slate-900 font-mono text-sm">{{ $inspection->inspection_date?->format('Y-m-d') }}</strong>
        </div>
        <div>
            <span class="text-slate-400 block font-bold">الجهة الموردة</span>
            <strong class="text-slate-900">{{ $inspection->source_display_name }}</strong>
        </div>
        <div>
            <span class="text-slate-400 block font-bold">القائم بالفحص</span>
            <strong class="text-slate-900">{{ $inspection->inspector_name }}</strong>
        </div>
        <div>
            <span class="text-slate-400 block font-bold">إذن الإضافة المخزنية</span>
            @if($inspection->stockMovement)
                <a href="{{ route('print.voucher', $inspection->stockMovement->id) }}" target="_blank" class="font-mono font-black text-emerald-700 hover:underline">
                    {{ $inspection->stockMovement->voucher_number }}
                </a>
            @else
                <span class="text-slate-500">لم يُرحّل للمخزن</span>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <h3 class="text-sm font-black text-slate-900 mb-4">تفاصيل الأصناف المفحوصة</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-4">اسم الصنف</th>
                        <th class="py-3 px-3 text-center">الوحدة</th>
                        <th class="py-3 px-4 text-center">الكمية الموردة</th>
                        <th class="py-3 px-4 text-center text-emerald-700">الكمية المقبولة</th>
                        <th class="py-3 px-4 text-center text-rose-600">الكمية المرفوضة</th>
                        <th class="py-3 px-4 text-center">النتيجة</th>
                        <th class="py-3 px-4">ملاحظات الفحص</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($inspection->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->item_name }}</td>
                            <td class="py-3 px-3 text-center">{{ $item->unit }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold">{{ number_format($item->delivered_quantity, 2) }}</td>
                            <td class="py-3 px-4 text-center font-mono font-black text-emerald-700">{{ number_format($item->accepted_quantity, 2) }}</td>
                            <td class="py-3 px-4 text-center font-mono font-black text-rose-600">{{ number_format($item->rejected_quantity, 2) }}</td>
                            <td class="py-3 px-4 text-center font-bold">{{ $item->result_arabic }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
