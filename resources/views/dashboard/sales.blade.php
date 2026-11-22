@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة المبيعات')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-emerald-800 to-brand-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                قسم المبيعات وخدمة العملاء
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا أبانوب مجدي (مدير المبيعات)</h1>
            <p class="text-emerald-100 text-sm max-w-xl">
                إدارة طلبات عملاء مطاعم مصر الكبرى، تخصيص أطقم الكوڤير (BOM)، وتحويل الطلبات لإدارة التشغيل للحصول على مواعيد التسليم الدقيقة.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.orders.create') }}" class="px-5 py-2.5 bg-emerald-400 hover:bg-emerald-300 text-brand-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل طلبية جديدة لعميل</span>
            </a>
            <a href="{{ route('sales.customers.create') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة عميل جديد</span>
            </a>
        </div>
    </div>

    <!-- Sales KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-building-user"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي العملاء المتعاقدين</p>
                <h3 class="text-xl font-black text-slate-800">{{ $totalCustomers }} <span class="text-xs font-medium text-slate-500">شركة ومطعم</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">طلبات المبيعات الجارية</p>
                <h3 class="text-xl font-black text-blue-700">{{ $activeOrdersCount }} <span class="text-xs font-medium text-slate-500">طلب</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي قيمة المبيعات</p>
                <h3 class="text-xl font-black text-slate-800">{{ number_format($totalSalesValue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border {{ $lossOrders->count() > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lossOrders->count() > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">عقود بأسعار ثابتة دون التكلفة</p>
                <h3 class="text-xl font-black {{ $lossOrders->count() > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lossOrders->count() }} <span class="text-xs font-medium text-slate-500">إنذار</span></h3>
            </div>
        </div>
    </div>

    <!-- Top Clients Quick Access & Logos -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-star text-amber-500"></i>
                <span>أهم العملاء المتعامل معهم (مع تصاميم ولوجوهات الشركات من الموقع)</span>
            </h2>
            <a href="{{ route('sales.customers') }}" class="text-xs font-bold text-brand-600 hover:underline">عرض كافة العملاء</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($topCustomers as $cust)
                <a href="{{ route('sales.customers.show', $cust->id) }}" class="p-3 rounded-2xl border border-slate-100 hover:border-brand-500 hover:shadow-md transition text-center space-y-2 group bg-slate-50/60">
                    <img src="{{ $cust->logo_url }}" alt="{{ $cust->name }}" class="w-16 h-16 mx-auto rounded-xl object-contain bg-white p-1 shadow-sm border border-slate-200 group-hover:scale-105 transition">
                    <div class="font-bold text-xs text-slate-800 truncate group-hover:text-brand-600">{{ $cust->name }}</div>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-semibold">
                        {{ $cust->sales_orders_count }} طلبية
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Sales Orders Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-brand-600"></i>
                <span>سجل طلبات المبيعات ومراحل دورة العمل</span>
            </h2>
            <a href="{{ route('sales.orders.create') }}" class="text-xs font-bold text-brand-600 hover:underline">+ تسجيل طلب</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم الطلب</th>
                        <th class="py-3 px-4">العميل</th>
                        <th class="py-3 px-4">المنتج والكمية</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">حالة الدورة الإدارية</th>
                        <th class="py-3 px-4">موعد التسليم</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">{{ $order->order_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $order->customer->name }}</td>
                            <td class="py-3 px-4">{{ $order->items->first()?->product->name ?? 'طلب متعدد' }}</td>
                            <td class="py-3 px-4 font-black">{{ number_format($order->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $order->status_badge['color'] }}">
                                    {{ $order->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium">
                                {{ $order->confirmed_delivery_date ? $order->confirmed_delivery_date->format('Y-m-d') : 'قيد المراجعة' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('sales.orders.show', $order->id) }}" class="px-3 py-1 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 rounded-lg font-bold text-xs transition">
                                    متابعة الطلب
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
