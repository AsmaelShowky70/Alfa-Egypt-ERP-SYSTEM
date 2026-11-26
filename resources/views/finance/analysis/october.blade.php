@extends('layouts.app')

@section('title', 'التحليل المالي لشهري سبتمبر وأكتوبر 2026')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-amber-500">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-2xl">
                <i class="fa-solid fa-file-excel"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>التقرير والتحليل المالي لشهري سبتمبر وأكتوبر 2026</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-500/30 text-amber-300 border border-amber-500/50">مطابقة ملف Excel</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">شيت المبيعات والضرائب (14% و 1%)، شيت المشتريات، كشف م غمومية (644K ج.م)، وشيت حركة الخزينة وتغطية عجز السيولة</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 no-print">
                <i class="fa-solid fa-print"></i>
                <span>طباعة التقرير</span>
            </button>
            <a href="{{ route('finance.index') }}" class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 no-print">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للمالية</span>
            </a>
        </div>
    </div>

    <!-- 4 High-Level KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مبيعات أكتوبر (شيت 1)</span>
                <h3 class="text-2xl font-black text-blue-700 font-mono">
                    {{ number_format($totalOctoberSales, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-500">صافي المبيعات: {{ number_format($totalOctoberNetSales, 0) }} ج.م</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مشتريات الخامات (شيت 2)</span>
                <h3 class="text-2xl font-black text-purple-700 font-mono">
                    {{ number_format($totalOctoberBills, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-500">المسدد للموردين: {{ number_format($totalOctoberPaidSuppliers, 0) }} ج.م</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">المصروفات العمومية والتشغيلية (شيت 3)</span>
                <h3 class="text-2xl font-black text-rose-700 font-mono">
                    {{ number_format($totalOctoberExpenses, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-500">إجمالي 18 بنداً تشغيلياً (م غمومية)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">صافي حركة السيولة والعجز (شيت 4)</span>
                <h3 class="text-2xl font-black {{ $octoberDeficit >= 0 ? 'text-emerald-700' : 'text-amber-600' }} font-mono">
                    {{ number_format($octoberDeficit, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-500">عجز السيولة المغطى من الشركاء والتمويل</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
    </div>

    <!-- Interactive Excel Tabs Navigation -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ tab: 'sales' }">
        <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 p-2 overflow-x-auto no-print">
            <button @click="tab = 'sales'" :class="tab === 'sales' ? 'bg-white text-blue-700 shadow-sm font-black' : 'text-slate-600 font-bold hover:bg-slate-200/60'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-table text-blue-600"></i>
                <span>1. المبيعات والعملاء والضرائب (14% & 1%)</span>
            </button>
            <button @click="tab = 'purchases'" :class="tab === 'purchases' ? 'bg-white text-purple-700 shadow-sm font-black' : 'text-slate-600 font-bold hover:bg-slate-200/60'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-truck-ramp-box text-purple-600"></i>
                <span>2. المشتريات ومسحوبات الموردين</span>
            </button>
            <button @click="tab = 'expenses'" :class="tab === 'expenses' ? 'bg-white text-rose-700 shadow-sm font-black' : 'text-slate-600 font-bold hover:bg-slate-200/60'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-receipt text-rose-600"></i>
                <span>3. م غمومية (المصروفات الـ 18 بند)</span>
            </button>
            <button @click="tab = 'cashflow'" :class="tab === 'cashflow' ? 'bg-white text-emerald-700 shadow-sm font-black' : 'text-slate-600 font-bold hover:bg-slate-200/60'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                <span>4. الخزنة وتدفقات السيولة (سبتمبر وأكتوبر)</span>
            </button>
        </div>

        <!-- TAB 1: Sales & Tax Analysis (Sheet 1) -->
        <div x-show="tab === 'sales'" class="p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">تحليل مبيعات أكتوبر 2026 وحساب الضرائب</h3>
                    <p class="text-xs text-slate-500">حصر فواتير العملاء مع تفقيط ضريبة القيمة المضافة 14% وضريبة خصم الأرباح 1% والصافي والمحصل والمتبقي</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold font-mono">
                    <span class="text-slate-600">ضريبة 14%: <b class="text-slate-900">{{ number_format($totalOctoberVAT, 2) }}</b> ج.م</span>
                    <span class="text-slate-600">خصم 1%: <b class="text-rose-700">{{ number_format($totalOctoberWithholding, 2) }}</b> ج.م</span>
                    <span class="text-emerald-700">المحصل: <b>{{ number_format($totalOctoberCollected, 2) }}</b> ج.م</span>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900 text-white font-bold">
                        <tr>
                            <th class="p-3">رقم الفاتورة</th>
                            <th class="p-3">اسم العميل</th>
                            <th class="p-3">قيمة الفاتورة الإجمالية</th>
                            <th class="p-3">ض.ق.م 14%</th>
                            <th class="p-3">خصم أرباح 1%</th>
                            <th class="p-3">صافي الاستحقاق</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">البيان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($salesInvoices as $inv)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-slate-900">{{ $inv->transaction_number }}</td>
                                <td class="p-3 font-bold text-blue-700">{{ $inv->customer->name ?? 'عميل' }}</td>
                                <td class="p-3 font-mono font-black text-slate-900">{{ number_format($inv->amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono text-slate-700">{{ number_format($inv->tax_vat_amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono text-rose-700">{{ number_format($inv->tax_withholding_amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono font-bold text-blue-700">{{ number_format($inv->net_amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono text-slate-500">{{ $inv->transaction_date ? $inv->transaction_date->format('Y-m-d') : '-' }}</td>
                                <td class="p-3 text-slate-600">{{ $inv->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-400">لا توجد فواتير مبيعات مسجلة لشهر أكتوبر</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: Purchases & Suppliers (Sheet 2) -->
        <div x-show="tab === 'purchases'" class="p-6 space-y-6" style="display: none;">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">تحليل مشتريات ومسحوبات الخامات لشهر أكتوبر 2026</h3>
                    <p class="text-xs text-slate-500">حصر فواتير الموردين والمسدد منها والمتبقي كالتزامات على الشركة</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold font-mono">
                    <span class="text-purple-700">إجمالي الفواتير: <b>{{ number_format($totalOctoberBills, 2) }}</b> ج.م</span>
                    <span class="text-emerald-700">المسدد للموردين: <b>{{ number_format($totalOctoberPaidSuppliers, 2) }}</b> ج.م</span>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900 text-white font-bold">
                        <tr>
                            <th class="p-3">رقم الفاتورة</th>
                            <th class="p-3">اسم المورد</th>
                            <th class="p-3">قيمة الفاتورة</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">طريقة الدفع</th>
                            <th class="p-3">البيان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($supplierBills as $bill)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-slate-900">{{ $bill->transaction_number }}</td>
                                <td class="p-3 font-bold text-purple-700">{{ $bill->supplier->name ?? 'مورد' }}</td>
                                <td class="p-3 font-mono font-black text-slate-900">{{ number_format($bill->amount, 2) }} ج.م</td>
                                <td class="p-3 font-mono text-slate-500">{{ $bill->transaction_date ? $bill->transaction_date->format('Y-m-d') : '-' }}</td>
                                <td class="p-3 font-bold text-slate-600">{{ $bill->payment_method }}</td>
                                <td class="p-3 text-slate-600">{{ $bill->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">لا توجد فواتير مشتريات مسجلة لشهر أكتوبر</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: General & Operating Expenses (Sheet 3: م غمومية) -->
        <div x-show="tab === 'expenses'" class="p-6 space-y-6" style="display: none;">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">شيت م غمومية (المصروفات العمومية والتشغيلية - أكتوبر)</h3>
                    <p class="text-xs text-slate-500">حصر الـ 18 بنداً تفصيلياً مطابقة لملف تحليل أكتوبر بإجمالي 644,073.00 ج.م</p>
                </div>
                <div class="text-xs font-bold font-mono text-rose-700">
                    الإجمالي العام: <b class="text-base">{{ number_format($totalOctoberExpenses, 2) }}</b> ج.م
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($expenseCategoriesGrouped as $cg)
                    @php
                        $share = $totalOctoberExpenses > 0 ? ($cg->total_amount / $totalOctoberExpenses) * 100 : 0;
                    @endphp
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">{{ $cg->category }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $cg->total_count }} سندات مرحلة</span>
                        </div>
                        <div class="text-left font-mono">
                            <span class="font-black text-slate-900 text-base block">{{ number_format($cg->total_amount, 2) }} ج.م</span>
                            <span class="text-[11px] font-bold text-rose-700">{{ number_format($share, 1) }}% من الإجمالي</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 4: Treasury Cash Flow (Sheet 4: الخزنة سبتمبر واكتوبر) -->
        <div x-show="tab === 'cashflow'" class="p-6 space-y-6" style="display: none;">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">شيت حركة الخزينة وتدفقات السيولة النقدية (سبتمبر وأكتوبر)</h3>
                    <p class="text-xs text-slate-500">مقارنة الوارد والمنصرف، تحديد فجوة السيولة النقدية، وآليات سد العجز عبر التمويل وجاري الشركاء</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Inflow Analysis -->
                <div class="bg-emerald-50/60 p-5 rounded-2xl border border-emerald-200 space-y-3">
                    <div class="flex items-center justify-between text-emerald-900">
                        <span class="font-black text-sm">المقبوضات النقدية والبنكية (الوارد)</span>
                        <i class="fa-solid fa-arrow-down-long text-lg"></i>
                    </div>
                    <div class="text-2xl font-black font-mono text-emerald-700">
                        {{ number_format($treasuryInflows, 2) }} ج.م
                    </div>
                    <ul class="text-xs text-emerald-900 space-y-1.5 pt-2 border-t border-emerald-200/60 font-medium">
                        <li>• تحصيلات عملاء كبار: كشري أبو طارق (866K ج.م)</li>
                        <li>• تحصيلات دينا فارمز: (324K ج.م)</li>
                        <li>• تحصيلات نقدية شركة روين: (200K ج.م)</li>
                    </ul>
                </div>

                <!-- Outflow Analysis -->
                <div class="bg-rose-50/60 p-5 rounded-2xl border border-rose-200 space-y-3">
                    <div class="flex items-center justify-between text-rose-900">
                        <span class="font-black text-sm">المدفوعات التشغيلية وسداد الخامات (المنصرف)</span>
                        <i class="fa-solid fa-arrow-up-long text-lg"></i>
                    </div>
                    <div class="text-2xl font-black font-mono text-rose-700">
                        {{ number_format($treasuryOutflows, 2) }} ج.م
                    </div>
                    <ul class="text-xs text-rose-900 space-y-1.5 pt-2 border-t border-rose-200/60 font-medium">
                        <li>• سداد موردي خامات وبوليمرات (حمزة، ريمون، علاء)</li>
                        <li>• مصروفات عمومية وتشغيلية لشهر أكتوبر: {{ number_format($totalOctoberExpenses, 0) }} ج.م</li>
                        <li>• مرتبات وعمالة ووقود وسولار للمصانع</li>
                    </ul>
                </div>

                <!-- Liquidity Gap & Deficit Handling -->
                <div class="bg-amber-50/60 p-5 rounded-2xl border border-amber-200 space-y-3">
                    <div class="flex items-center justify-between text-amber-900">
                        <span class="font-black text-sm">صافي حركة السيولة (العجز / الفائض)</span>
                        <i class="fa-solid fa-scale-balanced text-lg"></i>
                    </div>
                    <div class="text-2xl font-black font-mono text-amber-700">
                        {{ number_format($octoberDeficit, 2) }} ج.م
                    </div>
                    <div class="text-xs text-amber-900 leading-relaxed pt-2 border-t border-amber-200/60">
                        <b>تغطية فجوة السيولة النقدية:</b>
                        <p class="mt-1">تمت تغطية الفجوة من خلال دفعات تمويلية من جاري الشركاء وتدوير خطوط التسهيلات البنكية لضمان استمرارية تشغيل خطوط الإنتاج بدون أي توقف.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
