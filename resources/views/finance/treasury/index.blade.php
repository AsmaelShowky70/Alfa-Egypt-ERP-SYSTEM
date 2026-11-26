@extends('layouts.app')

@section('title', 'الخزينة والسيولة النقدية')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-emerald-600">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-2xl">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>حركة الخزينة والسيولة النقدية (Cash Flow)</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-300 border border-emerald-500/50">Treasury & Liquidity</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">متابعة الرصيد اللحظي للخزينة، الوارد من تحصيلات العملاء، المنصرف لسداد الموردين والمصروفات، وتسجيل سندات القبض والصرف</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openTreasuryModal()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل سند خزينة (قبض / صرف)</span>
            </button>
            <a href="{{ route('finance.index') }}" class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للمالية</span>
            </a>
        </div>
    </div>

    <!-- Treasury Liquidity KPI Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Current Total Balance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">الرصيد الفعلي الحالي بالخزينة</span>
                <h3 class="text-2xl font-black {{ $treasuryBalance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ number_format($treasuryBalance, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">الوارد الكلي - المنصرف الكلي</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- Inflows Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المقبوضات (الوارد)</span>
                <h3 class="text-2xl font-black text-emerald-700">
                    {{ number_format($inflows, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-emerald-600 font-medium">تحصيلات عملاء ومقبوضات نقدية</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-down-long"></i>
            </div>
        </div>

        <!-- Outflows Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المدفوعات (المنصرف)</span>
                <h3 class="text-2xl font-black text-rose-700">
                    {{ number_format($outflows, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-rose-600 font-medium">سداد موردين ومصروفات عمومية وتشغيل</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-up-long"></i>
            </div>
        </div>

        <!-- Month Performance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 block mb-1">صافي حركة الشهر الحالي</span>
                @php
                    $monthNet = $monthInflows - $monthOutflows;
                @endphp
                <h3 class="text-2xl font-black {{ $monthNet >= 0 ? 'text-teal-700' : 'text-amber-600' }}">
                    {{ number_format($monthNet, 2) }}
                    <span class="text-xs font-bold text-slate-500">ج.م</span>
                </h3>
                <span class="text-[11px] text-slate-500 font-medium">وارد الشهر: {{ number_format($monthInflows, 0) }} | منصرف: {{ number_format($monthOutflows, 0) }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- Date Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('finance.treasury') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">من تاريخ:</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">إلى تاريخ:</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                تصفية الحركات
            </button>
            @if(request()->hasAny(['from_date', 'to_date']))
                <a href="{{ route('finance.treasury') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء التصفية
                </a>
            @endif
        </form>
    </div>

    <!-- Treasury Movements Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                <span>دفتر يومية حركة الخزينة والمقبوضات / المدفوعات النقدية</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">رقم السند</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">الاتجاه</th>
                        <th class="p-3.5">النوع والتصنيف</th>
                        <th class="p-3.5">الطرف المعني</th>
                        <th class="p-3.5">طريقة الدفع</th>
                        <th class="p-3.5">وارد (مقبوضات)</th>
                        <th class="p-3.5">منصرف (مدفوعات)</th>
                        <th class="p-3.5">البيان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($transactions as $t)
                        @php
                            $isInflow = in_array($t->type, ['customer_payment', 'factory_internal_14_markup']);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">{{ $t->transaction_number }}</td>
                            <td class="p-3.5 font-mono text-slate-500">{{ $t->transaction_date ? $t->transaction_date->format('Y-m-d') : '-' }}</td>
                            <td class="p-3.5">
                                @if($isInflow)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 w-fit">
                                        <i class="fa-solid fa-arrow-down text-[9px]"></i>
                                        <span>وارد نقدية</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1 w-fit">
                                        <i class="fa-solid fa-arrow-up text-[9px]"></i>
                                        <span>منصرف نقدية</span>
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <span class="font-bold text-slate-800 block">{{ $t->type_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $t->category }}</span>
                            </td>
                            <td class="p-3.5 font-bold">
                                @if($t->customer)
                                    <span class="text-blue-700">{{ $t->customer->name }}</span>
                                @elseif($t->supplier)
                                    <span class="text-purple-700">{{ $t->supplier->name }}</span>
                                @elseif($t->factory)
                                    <span class="text-indigo-700">{{ $t->factory->name }}</span>
                                @else
                                    <span class="text-slate-400">إدارة الشركة</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-bold">
                                    {{ $t->payment_method }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-600 text-sm">
                                {{ $isInflow ? number_format($t->amount, 2) . ' ج.م' : '-' }}
                            </td>
                            <td class="p-3.5 font-mono font-bold text-rose-600 text-sm">
                                {{ !$isInflow ? number_format($t->amount, 2) . ' ج.م' : '-' }}
                            </td>
                            <td class="p-3.5 text-slate-600 max-w-xs truncate" title="{{ $t->description }}">{{ $t->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                لا توجد حركات خزينة مسجلة
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

<!-- Treasury Voucher Modal (Inflow / Outflow) -->
<div id="treasuryModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-emerald-700">
                <i class="fa-solid fa-vault text-xl"></i>
                <h3 class="font-black text-slate-900 text-base">تسجيل سند حركة خزينة</h3>
            </div>
            <button type="button" onclick="closeTreasuryModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.treasury.voucher') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">نوع الحركة (قبض أم صرف) *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="direction" value="inflow" checked onchange="updateVoucherAccounts('inflow')">
                        <span class="text-xs font-black text-emerald-700">قبض / توريد نقدية (وارد)</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="direction" value="outflow" onchange="updateVoucherAccounts('outflow')">
                        <span class="text-xs font-black text-rose-700">صرف نقدية (منصرف)</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ المعاملة *</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف *</label>
                    <input type="text" name="category" required placeholder="مثال: توريد نقدية / سداد نثريات" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الدفع *</label>
                    <select name="payment_method" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                        <option value="نقدي">نقدي (الخزينة الرئيسية)</option>
                        <option value="تحويل بنكي">تحويل بنكي</option>
                        <option value="شيك">شيك</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الطرف المدين (Debit) *</label>
                    <input type="text" id="voucherDebitAccount" name="debit_account" value="حـ/ الخزينة الرئيسية" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الطرف الدائن (Credit) *</label>
                    <input type="text" id="voucherCreditAccount" name="credit_account" value="حـ/ جاري الشركاء / إيرادات" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">البيان / تفاصيل السند *</label>
                <textarea name="description" rows="2" required placeholder="بيان تفصيلي عن سبب القبض أو الصرف..." class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">مرفق السند (إيصال / صورة شيك)</label>
                <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs px-2 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeTreasuryModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>ترحيل السند للخزينة</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTreasuryModal() {
        document.getElementById('treasuryModal').classList.remove('hidden');
    }

    function closeTreasuryModal() {
        document.getElementById('treasuryModal').classList.add('hidden');
    }

    function updateVoucherAccounts(dir) {
        if (dir === 'inflow') {
            document.getElementById('voucherDebitAccount').value = 'حـ/ الخزينة الرئيسية';
            document.getElementById('voucherCreditAccount').value = 'حـ/ إيرادات متنوعة / تمويل';
        } else {
            document.getElementById('voucherDebitAccount').value = 'حـ/ المصروفات / التزامات';
            document.getElementById('voucherCreditAccount').value = 'حـ/ الخزينة الرئيسية';
        }
    }
</script>
@endpush
@endsection
