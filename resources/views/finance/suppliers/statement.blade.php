@extends('layouts.app')

@section('title', 'كشف حساب مورد معتمد - ' . $supplier->name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Action Bar (hidden on print) -->
    <div class="flex items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.suppliers') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة لقائمة الموردين</span>
            </a>
            <span class="text-slate-300">|</span>
            <span class="text-xs text-slate-500 font-bold">كشف حساب المورد: <b>{{ $supplier->name }}</b></span>
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
                <div class="inline-block bg-purple-900 text-white font-black text-base px-4 py-1.5 rounded-xl">
                    كشف حساب مورد تفصيلي
                </div>
                <div class="text-xs text-slate-500 mt-2 font-mono">
                    تاريخ الاستخراج: <b>{{ date('Y-m-d') }}</b>
                </div>
            </div>
        </div>

        <!-- Supplier & Statement Meta Details -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs">
            <div class="space-y-1.5">
                <div>المورد: <b class="text-slate-900 text-sm">{{ $supplier->name }}</b></div>
                <div>كود المورد: <b class="font-mono text-slate-900">{{ $supplier->code }}</b></div>
                <div>تصنيف الخامات: <b class="text-purple-700">{{ $supplier->category }}</b></div>
                <div>المسؤول: <b>{{ $supplier->contact_person ?? 'الإدارة' }}</b></div>
            </div>
            <div class="space-y-1.5 text-left rtl:text-right">
                <div>الهاتف: <b class="font-mono">{{ $supplier->phone ?? 'غير مسجل' }}</b></div>
                <div>العنوان: <b>{{ $supplier->address ?? 'جمهورية مصر العربية' }}</b></div>
                <div>رصيد أول المدة: <b class="font-mono text-slate-900">{{ number_format($supplier->opening_balance, 2) }} ج.م</b></div>
                <div>
                    @if($supplier->opening_balance < 0)
                        <span class="text-emerald-700 font-bold">عربون مقدم مسجل بالدفاتر</span>
                    @else
                        <span class="text-purple-700 font-bold">مستحق للمورد من فترات سابقة</span>
                    @endif
                </div>
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
                        <th class="p-3">دائن (فواتير خامات)</th>
                        <th class="p-3">مدين (سداد وصرف)</th>
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
                        <td class="p-3 font-mono">{{ $supplier->opening_balance > 0 ? number_format($supplier->opening_balance, 2) : '-' }}</td>
                        <td class="p-3 font-mono">{{ $supplier->opening_balance < 0 ? number_format(abs($supplier->opening_balance), 2) : '-' }}</td>
                        <td class="p-3 font-mono text-slate-900 font-black">{{ number_format($supplier->opening_balance, 2) }} ج.م</td>
                    </tr>

                    @php
                        $running = $supplier->opening_balance;
                    @endphp

                    @forelse($transactions as $t)
                        @php
                            $isCredit = in_array($t->type, ['supplier_bill']);
                            if ($isCredit) {
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
                            <td class="p-3 font-mono font-bold text-purple-700">
                                {{ $isCredit ? number_format($t->amount, 2) : '-' }}
                            </td>
                            <td class="p-3 font-mono font-bold text-emerald-700">
                                {{ !$isCredit ? number_format($t->amount, 2) : '-' }}
                            </td>
                            <td class="p-3 font-mono font-black text-slate-900">
                                {{ number_format($running, 2) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-400">
                                لم يتم تسجيل حركات فواتير خامات أو صرف خلال هذه الفترة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Totals Box -->
        <div class="grid grid-cols-3 gap-4 p-5 bg-slate-900 text-white rounded-2xl">
            <div>
                <span class="text-xs text-slate-400 block mb-1">إجمالي فواتير الخامات (دائن)</span>
                <div class="text-lg font-black font-mono text-purple-300">
                    {{ number_format($supplier->total_billed, 2) }} ج.م
                </div>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">إجمالي المسدد للمورد (مدين)</span>
                <div class="text-lg font-black font-mono text-emerald-300">
                    {{ number_format($supplier->total_paid, 2) }} ج.م
                </div>
            </div>
            <div class="border-r border-slate-700 pr-4">
                <span class="text-xs text-slate-400 block mb-1">الرصيد النهائي الحالي</span>
                <div class="text-xl font-black font-mono {{ $supplier->current_balance >= 0 ? 'text-purple-300' : 'text-emerald-400' }}">
                    {{ number_format($supplier->current_balance, 2) }} ج.م
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $supplier->current_balance >= 0 ? 'مستحق السداد لصالح المورد' : 'عربون مدفوع مقدماً لصالح الفا إيجبت' }}
                </div>
            </div>
        </div>

        <!-- Official Signatures Block -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-3 gap-8 text-center text-xs">
            <div class="space-y-8">
                <div class="font-bold text-slate-700">مدير إدارة المشتريات</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">التوقيع والتاريخ</div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">مدير الإدارة المالية</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">التوقيع والختم المعتمد</div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">تأكيد ومطابقة المورد</div>
                <div class="border-b border-dashed border-slate-400 w-32 mx-auto"></div>
                <div class="text-[11px] text-slate-400">الاسم والصفة والختم</div>
            </div>
        </div>

    </div>

</div>
@endsection
