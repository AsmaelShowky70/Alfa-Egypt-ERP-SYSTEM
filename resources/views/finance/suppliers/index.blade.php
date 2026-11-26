@extends('layouts.app')

@section('title', 'حسابات والتزامات الموردين والعربونات')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-purple-600">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-600/20 border border-purple-500/40 flex items-center justify-center text-purple-400 text-2xl">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>حسابات والتزامات الموردين والعربونات المقدمة</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-purple-500/30 text-purple-300 border border-purple-500/50">Accounts Payable</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">مطابقة دفاتر إكسيل 23 نوفمبر، حصر مسحوبات الخامات، تسجيل سندات الصرف للموردين، ومتابعة العربونات المقدمة</p>
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
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي مستحقات الموردين (التزامات على الشركة)</span>
                <h3 class="text-2xl font-black text-purple-700">
                    {{ number_format($totalPayables, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">أرصدة دائنة مستحقة السداد</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المسدد للموردين (سندات الصرف)</span>
                <h3 class="text-2xl font-black text-emerald-600">
                    {{ number_format($totalPaid, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">تم صرفها نقداً أو بشيكات بنكية</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي العربونات والدفعات المقدمة لموردين</span>
                <h3 class="text-2xl font-black text-emerald-600">
                    {{ number_format($totalAdvances, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">أرصدة مدينة لصالح الفا إيجبت (حمزة السوري، محمد جمال)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('finance.suppliers') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto flex-1">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث باسم المورد، الكود، أو نوع الخامات..." class="w-full text-xs pr-9 pl-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <i class="fa-solid fa-search absolute right-3 top-3 text-slate-400 text-xs"></i>
            </div>
            <select name="filter" class="text-xs py-2.5 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500" onchange="this.form.submit()">
                <option value="">-- كافة حالات الأرصدة --</option>
                <option value="creditor" {{ request('filter') == 'creditor' ? 'selected' : '' }}>دائن (له مستحقات على الشركة واجبة السداد)</option>
                <option value="debtor" {{ request('filter') == 'debtor' ? 'selected' : '' }}>مدين (له عربون مدفوع مقدماً من الفا إيجبت)</option>
                <option value="settled" {{ request('filter') == 'settled' ? 'selected' : '' }}>مسدد بالكامل (الرصيد 0)</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition">
                تصفية
            </button>
            @if(request()->hasAny(['search', 'filter']))
                <a href="{{ route('finance.suppliers') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء التصفية
                </a>
            @endif
        </form>
    </div>

    <!-- Suppliers Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">المورد</th>
                        <th class="p-3.5">الكود</th>
                        <th class="p-3.5">التصنيف</th>
                        <th class="p-3.5">رصيد أول المدة</th>
                        <th class="p-3.5">إجمالي الفواتير (دائن)</th>
                        <th class="p-3.5">إجمالي المسدد (مدين)</th>
                        <th class="p-3.5">الرصيد الحالي</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($suppliers as $s)
                        @php
                            $bal = $s->current_balance;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5">
                                <a href="{{ route('finance.suppliers.statement', $s->id) }}" class="font-extrabold text-slate-900 hover:text-purple-600 transition block text-sm">
                                    {{ $s->name }}
                                </a>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $s->phone ?? 'بدون هاتف' }}</span>
                            </td>
                            <td class="p-3.5 font-mono text-slate-500 font-bold">{{ $s->code }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $s->category }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-800">
                                {{ number_format($s->opening_balance, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono text-purple-700 font-bold">
                                {{ number_format($s->total_billed, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono text-emerald-700 font-bold">
                                {{ number_format($s->total_paid, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono font-black text-sm">
                                @if($bal > 0)
                                    <span class="text-purple-700">{{ number_format($bal, 2) }} ج.م</span>
                                @elseif($bal < 0)
                                    <span class="text-emerald-700">({{ number_format(abs($bal), 2) }} ج.م) عربون</span>
                                @else
                                    <span class="text-slate-400">0.00 ج.م</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($bal > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        دائن (يستحق سداد)
                                    </span>
                                @elseif($bal < 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        عربون مقدم مسدد
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        مسدد بالكامل
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openSupplierPaymentModal({{ $s->id }}, '{{ addslashes($s->name) }}', {{ $bal }})" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <span>سند صرف</span>
                                    </button>
                                    <a href="{{ route('finance.suppliers.statement', $s->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
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

<!-- Supplier Payment / Disbursement Voucher Modal -->
<div id="supplierPaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-purple-700">
                <i class="fa-solid fa-receipt text-xl"></i>
                <h3 class="font-black text-slate-900 text-base">إصدار سند صرف نقدية / شيك لمورد</h3>
            </div>
            <button type="button" onclick="closeSupplierPaymentModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="supplierPaymentForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المورد</label>
                <input type="text" id="modalSupplierName" readonly class="w-full text-xs bg-slate-100 text-slate-700 font-bold px-3 py-2.5 rounded-xl border border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مبلغ السداد (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الصرف *</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الصرف *</label>
                    <select name="payment_method" id="supplierPaymentMethodSelect" onchange="toggleSupplierCheckFields(this.value)" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                        <option value="نقدي">نقدي (خصم من الخزينة الرئيسية)</option>
                        <option value="شيك">شيك بنكي (أوراق دفع للمورد)</option>
                        <option value="تحويل بنكي">تحويل بنكي من الحساب</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مرفق سند الصرف / إيصال الاستلام</label>
                    <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs px-2 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <!-- Check Details (Conditional) -->
            <div id="supplierCheckFields" class="grid grid-cols-2 gap-3 hidden bg-purple-50 p-3 rounded-xl border border-purple-200">
                <div>
                    <label class="block text-xs font-bold text-purple-900 mb-1">رقم الشيك الصادر</label>
                    <input type="text" name="check_number" placeholder="مثال: CHK-4455" class="w-full text-xs px-3 py-2 rounded-lg border border-purple-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-purple-900 mb-1">تاريخ استحقاق الشيك</label>
                    <input type="date" name="due_date" class="w-full text-xs px-3 py-2 rounded-lg border border-purple-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">البيان / ملاحظات السند *</label>
                <textarea name="description" rows="2" required placeholder="سند صرف سداد دفعة توريد خامات..." class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeSupplierPaymentModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>تأكيد وترحيل سند الصرف للمورد</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openSupplierPaymentModal(supplierId, supplierName, currentBalance) {
        const form = document.getElementById('supplierPaymentForm');
        form.action = `/finance/suppliers/${supplierId}/payment`;
        document.getElementById('modalSupplierName').value = supplierName + (currentBalance > 0 ? ` (المستحق له: ${Number(currentBalance).toLocaleString()} ج.م)` : '');
        document.getElementById('supplierPaymentModal').classList.remove('hidden');
    }

    function closeSupplierPaymentModal() {
        document.getElementById('supplierPaymentModal').classList.add('hidden');
    }

    function toggleSupplierCheckFields(val) {
        const checkFields = document.getElementById('supplierCheckFields');
        if (val === 'شيك') {
            checkFields.classList.remove('hidden');
        } else {
            checkFields.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
