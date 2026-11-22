@extends('layouts.app')

@section('title', 'أوامر التشغيل للخطوط')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-gears text-blue-600"></i>
                <span>أوامر التشغيل وسجل الإنتاج</span>
            </h1>
            <p class="text-xs text-slate-500">متابعة تنفيذ أوامر الشغل على خط تغليف الكوڤير ومصانع الوايبس والجوانتيات</p>
        </div>
        <a href="{{ route('operations.hourlyReports') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
            <i class="fa-solid fa-clock"></i>
            <span>تقارير الساعات</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم أمر الشغل</th>
                        <th class="py-3 px-4">المصنع / الخط</th>
                        <th class="py-3 px-4">المنتج المستهدف</th>
                        <th class="py-3 px-4">العميل المرتبط</th>
                        <th class="py-3 px-4">الكمية المخططة</th>
                        <th class="py-3 px-4">المنجز الفعلي</th>
                        <th class="py-3 px-4">نسبة الإنجاز</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workOrders as $wo)
                        @php
                            $pct = $wo->planned_quantity > 0 ? min(100, round(($wo->produced_quantity / $wo->planned_quantity) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $wo->order_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $wo->factory->name }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $wo->product->name }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $wo->salesOrder->customer->name ?? 'إنتاج عام' }}</td>
                            <td class="py-3 px-4 font-bold">{{ number_format($wo->planned_quantity) }} ق</td>
                            <td class="py-3 px-4 font-black text-emerald-700">{{ number_format($wo->produced_quantity) }} ق</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="font-bold text-[10px] text-slate-600">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $wo->status_badge['color'] }}">
                                    {{ $wo->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('operations.workOrders.show', $wo->id) }}" class="px-3 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-bold text-xs transition">
                                        متابعة الخط
                                    </a>
                                    <a href="{{ route('print.workOrder', $wo->id) }}" target="_blank" class="p-1 text-slate-400 hover:text-slate-600" title="طباعة أمر التشغيل">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">لا توجد أوامر تشغيل مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $workOrders->links() }}
        </div>
    </div>

</div>
@endsection
