@extends('layouts.app')

@section('title', 'أوامر الشراء والتوريد')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-purple-600"></i>
                <span>أوامر الشراء (موردين خارجيين + مصانع داخلية +14%)</span>
            </h1>
            <p class="text-xs text-slate-500">توثيق طلبات الشراء إلى الموردين أو الشراء الداخلي من خطوط الإنتاج محملة بـ 14% تكلفة تشغيل</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchasing.orders.create') }}" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إصدار أمر شراء جديد</span>
            </a>
            <a href="{{ route('purchasing.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للمشتريات
            </a>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم أمر الشراء</th>
                        <th class="py-3 px-4">نوع الشراء</th>
                        <th class="py-3 px-4">جهة التوريد</th>
                        <th class="py-3 px-4">المستودع المستلم</th>
                        <th class="py-3 px-4">تاريخ الأمر</th>
                        <th class="py-3 px-4">المبلغ الإجمالي</th>
                        <th class="py-3 px-4">تكلفة إنتاج 14%</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $po)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $po->po_number }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $po->is_internal_factory ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $po->is_internal_factory ? 'داخلي من خط إنتاج' : 'خارجي من مورد' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $po->is_internal_factory ? ($po->fromFactory->name ?? 'خط داخلي') : ($po->supplier->name ?? 'مورد خارجي') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $po->destination_warehouse }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $po->order_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($po->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-indigo-700 font-bold">
                                @if($po->internal_markup_amount > 0)
                                    +{{ number_format($po->internal_markup_amount, 2) }} ج.م
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $po->status === 'received_in_warehouse' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $po->status === 'received_in_warehouse' ? 'مستلم بالمخزن' : 'قيد التوريد' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('purchasing.orders.show', $po->id) }}" class="px-3 py-1 bg-purple-50 text-purple-800 hover:bg-purple-100 font-bold rounded-lg transition">
                                        عرض التفاصيل
                                    </a>
                                    <a href="{{ route('print.purchaseOrder', $po->id) }}" target="_blank" class="p-1 text-slate-400 hover:text-slate-700" title="طباعة أمر الشراء">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">لا توجد أوامر شراء مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
