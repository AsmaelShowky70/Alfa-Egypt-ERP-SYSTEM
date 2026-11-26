@extends('layouts.app')

@section('title', 'مستحقات وتحصيلات العملاء')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-blue-600">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-blue-400 text-2xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>مستحقات وأرصدة العملاء (شجرة الذمم المدينة)</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-500/30 text-blue-300 border border-blue-500/50">Accounts Receivable</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">مطابقة أرصدة العملاء من واقع دفاتر إكسيل 14 نوفمبر، حصر الفواتير، إصدار سندات القبض، وطباعة كشوف الحسابات المعتمدة</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.index') }}" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للمالية</span>
            </a>
            <a href="{{ route('finance.treasury') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-vault"></i>
                <span>الخزينة والسيولة</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مديونيات العملاء (مطلوبة للشركة)</span>
                <h3 class="text-2xl font-black text-blue-700">
                    {{ number_format($totalReceivables, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">أرصدة مدينة مستحقة الدفع</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي التحصيلات (سندات القبض المسجلة)</span>
                <h3 class="text-2xl font-black text-emerald-600">
                    {{ number_format($totalCollected, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">تم توريدها للخزينة والحسابات البنكية</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي الدفعات المقدمة (عربونات العملاء)</span>
                <h3 class="text-2xl font-black text-amber-600">
                    {{ number_format($totalAdvances, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">أرصدة دائنة لصالح العملاء (بالسالب)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-piggy-bank"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('finance.customers') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto flex-1">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث باسم العميل، الكود، أو رقم الهاتف..." class="w-full text-xs pr-9 pl-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fa-solid fa-search absolute right-3 top-3 text-slate-400 text-xs"></i>
            </div>
            <select name="filter" class="text-xs py-2.5 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">-- كافة حالات الأرصدة --</option>
                <option value="debtor" {{ request('filter') == 'debtor' ? 'selected' : '' }}>مدين (عليه مبالغ مستحقة للشركة)</option>
                <option value="creditor" {{ request('filter') == 'creditor' ? 'selected' : '' }}>دائن (له عربون / دفعة مقدمة)</option>
                <option value="settled" {{ request('filter') == 'settled' ? 'selected' : '' }}>مسدد بالكامل (الرصيد 0)</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                تصفية
            </button>
            @if(request()->hasAny(['search', 'filter']))
                <a href="{{ route('finance.customers') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء التصفية
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">العميل</th>
                        <th class="p-3.5">الكود</th>
                        <th class="p-3.5">رصيد أول المدة</th>
                        <th class="p-3.5">إجمالي الفواتير (مدين)</th>
                        <th class="p-3.5">إجمالي المحصل (دائن)</th>
                        <th class="p-3.5">الرصيد الحالي المستحق</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5">شروط السداد</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($customers as $c)
                        @php
                            $bal = $c->current_balance;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $c->logo_url }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                    <div>
                                        <a href="{{ route('finance.customers.statement', $c->id) }}" class="font-extrabold text-slate-900 hover:text-blue-600 transition block text-sm">
                                            {{ $c->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $c->phone ?? 'بدون هاتف' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 font-mono text-slate-500 font-bold">{{ $c->code }}</td>
                            <td class="p-3.5 font-mono font-bold text-slate-800">
                                {{ number_format($c->opening_balance, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono text-blue-700 font-bold">
                                {{ number_format($c->total_invoiced, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono text-emerald-700 font-bold">
                                {{ number_format($c->total_collected, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono font-black text-sm">
                                @if($bal > 0)
                                    <span class="text-blue-700">{{ number_format($bal, 2) }} ج.م</span>
                                @elseif($bal < 0)
                                    <span class="text-amber-600">({{ number_format(abs($bal), 2) }} ج.م) عربون</span>
                                @else
                                    <span class="text-slate-400">0.00 ج.م</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($bal > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        مدين (مستحق)
                                    </span>
                                @elseif($bal < 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        عربون مقدم
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        مسدد بالكامل
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-500 text-[11px]">{{ $c->payment_terms ?? 'آجل' }}</td>
                            <td class="p-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openPaymentModal({{ $c->id }}, '{{ addslashes($c->name) }}', {{ $bal }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>سند قبض</span>
                                    </button>
                                    <a href="{{ route('finance.customers.statement', $c->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-file-invoice"></i>
                                        <span>كشف حساب</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                لا توجد سجلات مطابقة للبحث
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Customer Payment / Receipt Voucher Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-emerald-700">
                <i class="fa-solid fa-receipt text-xl"></i>
                <h3 class="font-black text-slate-900 text-base">تسجيل سند قبض وتحصيل عميل</h3>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="paymentForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">اسم العميل</label>
                <input type="text" id="modalCustomerName" readonly class="w-full text-xs bg-slate-100 text-slate-700 font-bold px-3 py-2.5 rounded-xl border border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مبلغ التحصيل (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ التحصيل *</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة القبض *</label>
                    <select name="payment_method" id="paymentMethodSelect" onchange="toggleCheckFields(this.value)" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                        <option value="نقدي">نقدي (توريد للخزينة الرئيسية)</option>
                        <option value="شيك">شيك بنكي (أوراق قبض تحت التحصيل)</option>
                        <option value="تحويل بنكي">تحويل بنكي (الحساب البنكي)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مرفق سند القبض / صورة الشيك</label>
                    <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs px-2 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <!-- Check Details (Conditional) -->
            <div id="checkFields" class="grid grid-cols-2 gap-3 hidden bg-amber-50 p-3 rounded-xl border border-amber-200">
                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">رقم الشيك</label>
                    <input type="text" name="check_number" placeholder="مثال: CHK-12345" class="w-full text-xs px-3 py-2 rounded-lg border border-amber-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">تاريخ استحقاق الشيك</label>
                    <input type="date" name="due_date" class="w-full text-xs px-3 py-2 rounded-lg border border-amber-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">البيان / ملاحظات السند *</label>
                <textarea name="description" rows="2" required placeholder="سند قبض تحصيل دفعة مبيعات كوڤير..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>تأكيد وترحيل سند القبض للخزينة</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPaymentModal(customerId, customerName, currentBalance) {
        const form = document.getElementById('paymentForm');
        form.action = `/finance/customers/${customerId}/payment`;
        document.getElementById('modalCustomerName').value = customerName + (currentBalance > 0 ? ` (المستحق: ${Number(currentBalance).toLocaleString()} ج.م)` : '');
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function toggleCheckFields(val) {
        const checkFields = document.getElementById('checkFields');
        if (val === 'شيك') {
            checkFields.classList.remove('hidden');
        } else {
            checkFields.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
