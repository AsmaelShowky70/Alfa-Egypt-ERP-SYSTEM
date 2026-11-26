@extends('layouts.app')

@section('title', 'الإدارة المالية والحسابات العامة')

@section('content')
<div class="space-y-6">

    <!-- Top Executive Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-red-600">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-red-600/20 border border-red-500/40 flex items-center justify-center text-red-400 text-2xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white flex items-center gap-2">
                        <span>الإدارة المالية والحسابات المركزية</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-500/30 text-red-300 border border-red-500/50">ERP Alfa Financial</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">نظام القيود المزدوجة (Odoo-Style)، رصيد الخزينة اللحظي، شجرة العملاء والموردين، والتحليل المالي لشهري سبتمبر وأكتوبر</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.treasury') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-vault"></i>
                <span>الخزينة والسيولة</span>
            </a>
            <a href="{{ route('finance.customers') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-users"></i>
                <span>مستحقات العملاء</span>
            </a>
            <a href="{{ route('finance.suppliers') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-handshake"></i>
                <span>الموردين والعربونات</span>
            </a>
            <a href="{{ route('finance.journal') }}" class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-black border border-neutral-700 shadow transition flex items-center gap-2">
                <i class="fa-solid fa-book-journal-whills"></i>
                <span>دفتر اليومية العامة</span>
            </a>
            <a href="{{ route('finance.analysis.october') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-black rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i>
                <span>تحليل أكتوبر المالي</span>
            </a>
        </div>
    </div>

    <!-- Quick Financial Highlights (Row 1: Liquidity & Balances) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Cash in Treasury -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition">
            <div class="absolute -right-3 -bottom-3 text-emerald-50 text-7xl font-black pointer-events-none group-hover:scale-110 transition">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">رصيد الخزينة والسيولة الحالية</span>
                    <h3 class="text-2xl font-black {{ $treasuryBalance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ number_format($treasuryBalance, 2) }}
                        <span class="text-xs font-bold text-slate-500">ج.م</span>
                    </h3>
                    <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-2 font-medium">
                        <span class="text-emerald-700">وارد: {{ number_format($cashInflows, 0) }}</span>
                        <span>|</span>
                        <span class="text-rose-700">منصرف: {{ number_format($cashOutflows, 0) }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <!-- Accounts Receivable (العملاء) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-blue-300 transition">
            <div class="absolute -right-3 -bottom-3 text-blue-50 text-7xl font-black pointer-events-none group-hover:scale-110 transition">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مديونيات العملاء (مستحقات لنا)</span>
                    <h3 class="text-2xl font-black text-blue-700">
                        {{ number_format($totalReceivables, 2) }}
                        <span class="text-xs font-bold text-slate-500">ج.م</span>
                    </h3>
                    <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-1 font-medium">
                        <span class="text-slate-600">عربونات للعملاء: <b>{{ number_format($totalCustomerPrepayments, 2) }}</b> ج.م</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
        </div>

        <!-- Accounts Payable (الموردين) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-purple-300 transition">
            <div class="absolute -right-3 -bottom-3 text-purple-50 text-7xl font-black pointer-events-none group-hover:scale-110 transition">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مستحقات الموردين (التزامات علينا)</span>
                    <h3 class="text-2xl font-black text-purple-700">
                        {{ number_format($totalPayables, 2) }}
                        <span class="text-xs font-bold text-slate-500">ج.م</span>
                    </h3>
                    <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-1 font-medium">
                        <span class="text-emerald-700">عربونات مقدمة لموردين: <b>{{ number_format($totalSupplierAdvances, 2) }}</b> ج.م</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fa-solid fa-dolly"></i>
                </div>
            </div>
        </div>

        <!-- Operating Net Profit -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-teal-300 transition">
            <div class="absolute -right-3 -bottom-3 text-teal-50 text-7xl font-black pointer-events-none group-hover:scale-110 transition">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">صافي الأرباح التشغيلية التقديرية</span>
                    <h3 class="text-2xl font-black {{ $netProfit >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                        {{ number_format($netProfit, 2) }}
                        <span class="text-xs font-bold text-slate-500">ج.م</span>
                    </h3>
                    <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-1 font-medium">
                        <span class="text-indigo-600">أرباح 14% المحملة: <b>{{ number_format($markup14Total, 2) }}</b> ج.م</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Hub Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Customers Hub -->
        <a href="{{ route('finance.customers') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">حسابات العملاء</span>
                </div>
                <h4 class="font-black text-slate-900 text-base">سجل ومستحقات العملاء</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    متابعة أرصدة العملاء، إصدار سندات قبض فورية، كشف حساب تفصيلي للعميل، وتتبع مديونيات كبار العملاء (كشري أبو طارق، شركة روين، دينا فارمز).
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-700">
                <span>فتح شجرة العملاء</span>
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>

        <!-- Suppliers Hub -->
        <a href="{{ route('finance.suppliers') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-purple-400 transition flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-truck-moving"></i>
                    </div>
                    <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">حسابات الموردين</span>
                </div>
                <h4 class="font-black text-slate-900 text-base">الموردين والعربونات المقدمة</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    فواتير مسحوبات الخامات، سندات الصرف للموردين، كشف حساب المورد، وتتبع العربونات المقدمة المسجلة (حمزة السوري، محمد جمال العاشر).
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-purple-700">
                <span>فتح حسابات الموردين</span>
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>

        <!-- General Expenses Hub -->
        <a href="{{ route('finance.expenses') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-rose-400 transition flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">المصروفات العمومية</span>
                </div>
                <h4 class="font-black text-slate-900 text-base">المصروفات العمومية والتشغيلية</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    تبويب 18 بنداً تفصيلياً (سولار، صيانة ونظافة، إكراميات، بوفيه ووجبات، إيجار، تأمينات) مطابقة لكشف م غمومية لشهر أكتوبر (644K ج.م).
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-700">
                <span>عرض تبويب المصروفات</span>
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>

        <!-- October Financial Analysis Hub -->
        <a href="{{ route('finance.analysis.october') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 transition flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-table"></i>
                    </div>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">تحليل أكتوبر</span>
                </div>
                <h4 class="font-black text-slate-900 text-base">التحليل المالي لشهري سبتمبر وأكتوبر</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    عرض تفصيلي متطابق مع ملف الإكسيل: المبيعات والضرائب (14% و 1%)، المشتريات، المصروفات العمومية، وتدفقات الخزينة وعجز السيولة.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-700">
                <span>فتح التقرير المالي الشامل</span>
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>
    </div>

    <!-- Factory Profit & Loss Performance Matrix (مراكز أرباح المصانع الـ 6) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-industry text-indigo-600"></i>
                    <span>قائمة الأرباح والخسائر لمراكز الإنتاج وخطوط التصنيع المستقلة (14% تشغيل داخلي)</span>
                </h2>
                <p class="text-xs text-slate-500">تحميل نسبة الـ 14% على أوامر التوريد الداخلية لمتابعة ربحية كل مصنع كوحدة مستقلة</p>
            </div>
            <a href="{{ route('factories.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">عرض كافة المصانع</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($factoryPL as $item)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-sm text-slate-900">{{ $item['factory']->name }}</span>
                        <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                            +{{ $item['factory']->markup_percentage }}%
                        </span>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>إيرادات التوريد الداخلي:</span>
                            <b class="text-slate-900 font-mono">{{ number_format($item['revenue'], 2) }} ج.م</b>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>التكاليف المباشرة والتشغيل:</span>
                            <b class="text-rose-700 font-mono">{{ number_format($item['cost'], 2) }} ج.م</b>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                            <span class="font-bold">هامش أرباح المصنع:</span>
                            <b class="font-mono text-sm {{ $item['profit'] >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                                {{ number_format($item['profit'], 2) }} ج.م
                            </b>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Double-Entry Ledger Transactions Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-teal-600"></i>
                    <span>أحدث القيود والحركات المالية المعتمدة في السيستم</span>
                </h3>
                <p class="text-xs text-slate-500">حصر فواتير المبيعات، سندات القبض، فواتير الموردين، وسندات الصرف والمصروفات</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.journal') }}" class="text-xs font-bold text-teal-700 bg-teal-50 px-3 py-1.5 rounded-lg hover:bg-teal-100 transition">
                    عرض كامل دفتر اليومية العامة (Double-Entry)
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">رقم القيد / السند</th>
                        <th class="p-3.5">النوع</th>
                        <th class="p-3.5">الطرف (العميل / المورد)</th>
                        <th class="p-3.5">الطرف المدين (Debit)</th>
                        <th class="p-3.5">الطرف الدائن (Credit)</th>
                        <th class="p-3.5">المبلغ</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">طريقة الدفع</th>
                        <th class="p-3.5">البيان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">{{ $t->transaction_number }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $t->badge_class }}">
                                    {{ $t->type_name }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold">
                                @if($t->customer)
                                    <a href="{{ route('finance.customers.statement', $t->customer_id) }}" class="text-blue-700 hover:underline">
                                        {{ $t->customer->name }}
                                    </a>
                                @elseif($t->supplier)
                                    <a href="{{ route('finance.suppliers.statement', $t->supplier_id) }}" class="text-purple-700 hover:underline">
                                        {{ $t->supplier->name }}
                                    </a>
                                @elseif($t->factory)
                                    <span class="text-indigo-700">{{ $t->factory->name }}</span>
                                @else
                                    <span class="text-slate-400">حساب عام</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $t->debit_account ?? 'حـ/ الخزينة' }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $t->credit_account ?? 'حـ/ إيرادات' }}</td>
                            <td class="p-3.5 font-mono font-black text-slate-900 text-sm">
                                {{ number_format($t->amount, 2) }} ج.م
                            </td>
                            <td class="p-3.5 text-slate-500 font-mono">{{ $t->transaction_date ? $t->transaction_date->format('Y-m-d') : '-' }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-bold">
                                    {{ $t->payment_method }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-600 max-w-xs truncate" title="{{ $t->description }}">{{ $t->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                لا توجد معاملات مالية مسجلة بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
