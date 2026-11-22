@extends('layouts.app')

@section('title', 'طلبات المبيعات')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-brand-600"></i>
                <span>طلبات المبيعات وعقود العملاء</span>
            </h1>
            <p class="text-xs text-slate-500">متابعة كافة طلبات العملاء وتدفقها عبر مراحل التشغيل والمخزن والمشتريات</p>
        </div>
        <a href="{{ route('sales.orders.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>تسجيل طلبية جديدة لعميل</span>
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">العميل</th>
                        <th class="py-3.5 px-4">تاريخ الطلب</th>
                        <th class="py-3.5 px-4">المنتجات والكميات</th>
                        <th class="py-3.5 px-4">المبلغ الإجمالي</th>
                        <th class="py-3.5 px-4">مرحلة الدورة الإدارية</th>
                        <th class="py-3.5 px-4">موعد التسليم المعتمد</th>
                        <th class="py-3.5 px-4">إنذار الخسارة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $order->order_number }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2 font-bold text-slate-900">
                                    <img src="{{ $order->customer->logo_url }}" class="w-7 h-7 rounded-lg object-contain bg-slate-50 p-0.5 border border-slate-200">
                                    <span>{{ $order->customer->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $order->order_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-slate-700">
                                @foreach($order->items as $it)
                                    <div>{{ $it->product->name }} <b class="text-brand-800">({{ number_format($it->quantity) }} ق)</b></div>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($order->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $order->status_badge['color'] }}">
                                    {{ $order->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $order->confirmed_delivery_date ? $order->confirmed_delivery_date->format('Y-m-d') : 'قيد فحص التشغيل' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($order->has_loss_alarm)
                                    <span class="inline-flex items-center gap-1 text-[11px] bg-rose-100 text-rose-700 font-bold px-2.5 py-0.5 rounded-full animate-pulse">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        سعر أقل من التكلفة
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-bold text-xs"><i class="fa-solid fa-check"></i> رابح</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('sales.orders.show', $order->id) }}" class="px-3 py-1 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg font-bold text-xs transition">
                                        متابعة الدورة
                                    </a>
                                    <a href="{{ route('print.salesInvoice', $order->id) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700" title="طباعة فاتورة العميل">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">لا توجد طلبات مبيعات مسجلة حتى الآن.</td>
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
