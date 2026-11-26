@extends('layouts.app')

@section('title', 'كشف حساب عميل معتمد - ' . $customer->name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Action Bar (hidden on print) -->
    <div class="flex items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.customers') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة لقائمة العملاء</span>
            </a>
            <span class="text-slate-300">|</span>
            <span class="text-xs text-slate-500 font-bold">كشف حساب العميل: <b>{{ $customer->name }}</b></span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-black shadow transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>طباعة كشف الحساب (A4)</span>
            </button>
        </div>
    </div>

    <!-- Official Printable A4 Document Sheet -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-8 sm:p-12 space-y-8 text-slate-800 print:border-none print:shadow-none print:p-0">

        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol_nav.png') }}" class="h-16 w-auto">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت</h1>
                    <div class="text-xs font-bold text-slate-600">لصناعة مواد التغليف الحديث وتعبئة الكوڤير المتكامل</div>
                    <div class="text-[11px] text-slate-500 font-medium">س.ت: 184592 | ب.ض: 541-890-234 | العاشر من رمضان - مصر</div>
                </div>
            </div>
            <div class="text-left">
                <div class="inline-block bg-slate-900 text-white font-black text-base px-4 py-1.5 rounded-xl">
                    كشف حساب عميل تفصيلي
                </div>
                <div class="text-xs text-slate-500 mt-2 font-mono">
                    تاريخ الاستخراج: <b>{{ date('Y-m-d') }}</b>
                </div>
            </div>
        </div>

        <!-- Customer & Statement Meta Details -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs">
            <div class="space-y-1.5">
                <div>العميل: <b class="text-slate-900 text-sm">{{ $customer->name }}</b></div>
                <div>كود العميل: <b class="font-mono text-slate-900">{{ $customer->code }}</b></div>
                <div>المسؤول: <b>{{ $customer->contact_person ?? 'الإدارة' }}</b></div>
                <div>الهاتف: <b class="font-mono">{{ $customer->phone ?? 'غير مسجل' }}</b></div>
            </div>
            <div class="space-y-1.5 text-left rtl:text-right">
                <div>العنوان: <b>{{ $customer->address ?? 'جمهورية مصر العربية' }}</b></div>
                <div>الرقم الضريبي: <b class="font-mono">{{ $customer->tax_number ?? 'غير مسجل' }}</b></div>
                <div>شروط السداد: <b class="text-blue-700">{{ $customer->payment_terms ?? 'آجل 30 يوم' }}</b></div>
                <div>رصيد أول المدة: <b class="font-mono text-slate-900">{{ number_format($customer->opening_balance, 2) }} ج.م</b></div>
            </div>
        </div>

        <!-- Transactions Ledger Table -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-900 text-white font-bold">
                    <tr>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3">رقم المستند</th>
                        <th class="p-3">نوع العملية</th>
                        <th class="p-3">البيان</th>
                        <th class="p-3">مدين (فواتير)</th>
                        <th class="p-3">دائن (سداد)</th>
                        <th class="p-3">الرصيد التراكمي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-slate-700">
                    <!-- Opening Balance Row -->
                    <tr class="bg-slate-50 font-bold">
                        <td class="p-3 font-mono">-</td>
                        <td class="p-3 font-mono">OP-BAL</td>
                        <td class="p-3">رصيد أول المدة</td>
                        <td class="p-3">الرصيد الافتتاحي المعتمد من واقع الدفاتر</td>
                        <td class="p-3 font-mono">{{ $customer->opening_balance > 0 ? number_format($customer->opening_balance, 2) : '-' }}</td>
                        <td class="p-3 font-mono">{{ $customer->opening_balance < 0 ? number_format(abs($customer->opening_balance), 2) : '-' }}</td>
                        <td class="p-3 font-mono text-slate-900 font-black">{{ number_format($customer->opening_balance, 2) }} ج.م</td>
                    </tr>

                    @php
                        $running = $customer->opening_balance;
                    @endphp

                    @forelse($transactions as $t)
                        @php
                            $isDebit = in_array($t->type, ['sales_invoice', 'debit_note']);
                            if ($isDebit) {
                                $running += (float)$t->amount;
                            } else {
                                $running -= (float)$t->amount;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 font-mono">{{ $t->transaction_date ? $t->transaction_date->format('Y-m-d') : '-' }}</td>
                            <td class="p-3 font-mono font-bold">{{ $t->transaction_number }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $t->badge_class }}">
                                    {{ $t->type_name }}
                                </span>
                            </td>
                            <td class="p-3">{{ $t->description }}</td>
                            <td class="p-3 font-mono font-bold text-blue-700">
                                {{ $isDebit ? number_format($t->amount, 2) : '-' }}
                            </td>
                            <td class="p-3 font-mono font-bold text-emerald-700">
                                {{ !$isDebit ? number_format($t->amount, 2) : '-' }}
                            </td>
                            <td class="p-3 font-mono font-black text-slate-900">
                                {{ number_format($running, 2) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-400">
                                لم يتم تسجيل حركات مبيعات أو تحصيل خلال هذه الفترة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Totals Box -->
        <div class="grid grid-cols-3 gap-4 p-5 bg-slate-900 text-white rounded-2xl">
            <div>
                <span class="text-xs text-slate-400 block mb-1">إجمالي الفواتير المسحوبة (مدين)</span>
                <div class="text-lg font-black font-mono text-blue-300">
                    {{ number_format($customer->total_invoiced, 2) }} ج.م
                </div>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">إجمالي المسدد والمحصل (دائن)</span>
                <div class="text-lg font-black font-mono text-emerald-300">
                    {{ number_format($customer->total_collected, 2) }} ج.م
                </div>
            </div>
            <div class="border-r border-slate-700 pr-4">
                <span class="text-xs text-slate-400 block mb-1">الرصيد النهائي المستحق</span>
                <div class="text-xl font-black font-mono {{ $customer->current_balance >= 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    {{ number_format($customer->current_balance, 2) }} ج.م
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $customer->current_balance >= 0 ? 'مديونية مستحقة على العميل' : 'عربون / رصيد دائن لصالح العميل' }}
                </div>
            </div>
        </div>

        <!-- Official Signatures Block -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-3 gap-8 text-center text-xs">
            <div class="space-y-8">
                <div class="font-bold text-slate-700">المحاسب المالي المختص</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">التوقيع والتاريخ</div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">مدير الإدارة المالية</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">التوقيع والختم المعتمد</div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">موافقة واعتماد العميل</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">الاسم والصفة والختم</div>
            </div>
        </div>

    </div>

</div>
@endsection
