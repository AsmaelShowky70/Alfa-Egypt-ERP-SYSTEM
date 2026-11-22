@extends('layouts.app')

@section('title', 'إدارة المشتريات والتوريدات')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cart-flatbed text-purple-600"></i>
                <span>المشتريات وسلاسل الإمداد والتوريد</span>
            </h1>
            <p class="text-xs text-slate-500">توفير خامات الكوڤير الناقصة، التنسيق مع الموردين والمصانع الداخلية (+14%)، وتحديد مدد التوريد</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchasing.orders.create') }}" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إصدار أمر شراء (+14% داخلي)</span>
            </a>
            <a href="{{ route('purchasing.suppliers') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                سجل الموردين
            </a>
        </div>
    </div>

    <!-- Active Requisitions Awaiting Lead Time Quote -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-bell-concierge text-purple-600"></i>
                <span>طلبات النواقص الواردة من المخزن المركزي ({{ $pendingRequisitions->count() }})</span>
            </h2>
            <a href="{{ route('purchasing.requisitions') }}" class="text-xs font-bold text-purple-600 hover:underline">عرض الكل</a>
        </div>

        <div class="space-y-3">
            @forelse($pendingRequisitions as $pr)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-xs bg-purple-100 text-purple-900 px-2 py-0.5 rounded">{{ $pr->pr_number }}</span>
                            <span class="font-extrabold text-sm text-slate-900">لصالح العميل: {{ $pr->salesOrder->customer->name ?? 'مخزن عام' }}</span>
                        </div>
                        <p class="text-xs text-slate-500">السبب: {{ $pr->reason }}</p>
                    </div>

                    <a href="{{ route('purchasing.requisitions.show', $pr->id) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-clock"></i>
                        <span>تحديد مدة التوريد وإصدار أمر الشراء</span>
                    </a>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    لا توجد طلبات نواقص معلقة حالياً.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Active Purchase Orders Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-purple-600"></i>
                <span>أوامر الشراء الجارية للموردين والخطوط الداخلية</span>
            </h2>
            <a href="{{ route('purchasing.orders') }}" class="text-xs font-bold text-purple-600 hover:underline">عرض كل أوامر الشراء</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم أمر الشراء</th>
                        <th class="py-3 px-4">نوع الشراء</th>
                        <th class="py-3 px-4">جهة التوريد</th>
                        <th class="py-3 px-4">المستودع المستلم</th>
                        <th class="py-3 px-4">المبلغ الإجمالي</th>
                        <th class="py-3 px-4">هامش 14% داخلي</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($activePurchaseOrders as $po)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $po->po_number }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $po->is_internal_factory ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $po->is_internal_factory ? 'شراء من خط إنتاج داخلي' : 'شراء من مورد خارجي' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $po->is_internal_factory ? ($po->fromFactory->name ?? 'خط داخلي') : ($po->supplier->name ?? 'مورد خارجي') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $po->destination_warehouse }}</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($po->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-indigo-700 font-bold">+{{ number_format($po->internal_markup_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $po->status === 'received_in_warehouse' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $po->status === 'received_in_warehouse' ? 'مستلم بالمخزن' : 'جاري التوريد' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('purchasing.orders.show', $po->id) }}" class="px-3 py-1 bg-purple-50 text-purple-800 hover:bg-purple-100 font-bold rounded-lg transition">
                                    تفاصيل التوريد
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
