@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة الحسابات والمالية')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-teal-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-teal-500/20 text-teal-300 border border-teal-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                الإدارة المالية والتكاليف
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا عادل صادق (مدير الحسابات)</h1>
            <p class="text-teal-100 text-sm max-w-xl">
                إدارة الفواتير والمصروفات الثابتة والمتغيرة، احتساب هامش الـ 14% المحمل للمصانع الداخلية، تسعير المنتجات، ومراقبة جرس إنذار الخسارة (Loss Alarms).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.transactions.create') }}" class="px-5 py-2.5 bg-teal-400 hover:bg-teal-300 text-teal-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل قيد / فاتورة / سند صرف</span>
            </a>
            <a href="{{ route('finance.pricing') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-tags"></i>
                <span>محرك التسعير وتكاليف BOM</span>
            </a>
        </div>
    </div>

    <!-- Financial KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي المقبوضات المحصلة</p>
                <h3 class="text-xl font-black text-slate-800">{{ number_format($totalRevenue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">التكاليف الثابتة (رواتب/إيجار/مرافق)</p>
                <h3 class="text-xl font-black text-rose-700">{{ number_format($fixedCosts, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي هوامش المصانع 14%</p>
                <h3 class="text-xl font-black text-indigo-700">{{ number_format($markup14Total, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border {{ $lossAlertOrders->count() > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lossAlertOrders->count() > 0 ? 'bg-rose-100 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إنذارات البيع دون التكلفة</p>
                <h3 class="text-xl font-black {{ $lossAlertOrders->count() > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lossAlertOrders->count() }} <span class="text-xs font-medium text-slate-500">عقد</span></h3>
            </div>
        </div>
    </div>

    <!-- Loss Alarm Monitor (إنذار الخسارة وتثبيت السعر) -->
    @if($lossAlertOrders->count() > 0)
    <div class="bg-rose-50 border-2 border-rose-300 rounded-3xl p-6 shadow-md space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shadow-md animate-bounce">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h2 class="text-base font-black text-rose-950">شاشة جرس الإنذار: عقود كوڤير تباع بسعر ثابت أقل من التكلفة المباشرة</h2>
                    <p class="text-xs text-rose-700">السعر ثابت للعميل حفاظاً على التعاقد، ولكن تم تسجيل التنبيه لإحاطة الإدارة والحسابات وتعديل السعر مستقبلاً</p>
                </div>
            </div>
            <a href="{{ route('finance.lossAlarms') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow">
                تقرير تحليل الخسائر
            </a>
        </div>

        <div class="overflow-x-auto bg-white rounded-2xl border border-rose-200">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-rose-100/60 text-rose-900 font-bold border-b border-rose-200">
                        <th class="py-2.5 px-4">رقم الطلب</th>
                        <th class="py-2.5 px-4">العميل</th>
                        <th class="py-2.5 px-4">المنتج</th>
                        <th class="py-2.5 px-4">سعر البيع التعاقدي</th>
                        <th class="py-2.5 px-4">التكلفة الفعلية للقطعة</th>
                        <th class="py-2.5 px-4">صافي العجز بالقطعة</th>
                        <th class="py-2.5 px-4">إجمالي خسارة الطلبية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-100">
                    @foreach($lossAlertOrders as $lo)
                        @php
                            $item = $lo->items->first();
                            $pPrice = $item->unit_price ?? 0;
                            $pCost = $item->unit_cost ?? 0;
                            $lossPerUnit = $pCost - $pPrice;
                            $totalLoss = $lossPerUnit * ($item->quantity ?? 1);
                        @endphp
                        <tr>
                            <td class="py-2.5 px-4 font-mono font-bold">{{ $lo->order_number }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-800">{{ $lo->customer->name }}</td>
                            <td class="py-2.5 px-4">{{ $item->product->name ?? 'كوڤير' }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-700">{{ number_format($pPrice, 2) }} ج.م</td>
                            <td class="py-2.5 px-4 font-bold text-rose-700">{{ number_format($pCost, 2) }} ج.م</td>
                            <td class="py-2.5 px-4 font-black text-rose-700">-{{ number_format($lossPerUnit, 2) }} ج.م</td>
                            <td class="py-2.5 px-4 font-black text-rose-800">-{{ number_format($totalLoss, 2) }} ج.م</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Financial Transactions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-numeric text-teal-600"></i>
                <span>حركة القيود والفواتير والمصروفات الأخيرة</span>
            </h2>
            <a href="{{ route('finance.transactions.create') }}" class="text-xs font-bold text-teal-600 hover:underline">+ تسجيل قيد جديد</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم المعاملة</th>
                        <th class="py-3 px-4">التصنيف</th>
                        <th class="py-3 px-4">البيان والشرح</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4">المصنع المرتبط</th>
                        <th class="py-3 px-4">المرفق / الفاتورة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentTransactions as $txn)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">{{ $txn->transaction_number }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-50 text-teal-800">
                                    {{ $txn->category }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700 max-w-xs truncate">{{ $txn->description }}</td>
                            <td class="py-3 px-4 font-black {{ str_contains($txn->type, 'payment') || str_contains($txn->type, 'markup') ? 'text-emerald-700' : 'text-slate-800' }}">
                                {{ number_format($txn->amount, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $txn->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $txn->factory->name ?? 'عام / مركزي' }}</td>
                            <td class="py-3 px-4">
                                @if($txn->attachment)
                                    <a href="{{ asset($txn->attachment) }}" target="_blank" class="text-blue-600 hover:underline font-bold text-[11px] flex items-center gap-1">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>عرض الفاتورة</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">بدون مرفق</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
