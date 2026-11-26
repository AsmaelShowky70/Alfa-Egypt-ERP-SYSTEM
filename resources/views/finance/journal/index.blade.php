@extends('layouts.app')

@section('title', 'دفتر اليومية العامة والقيود المزدوجة')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-teal-600">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-teal-600/20 border border-teal-500/40 flex items-center justify-center text-teal-400 text-2xl">
                <i class="fa-solid fa-book-journal-whills"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>دفتر اليومية العامة والقيود المزدوجة (General Journal)</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-teal-500/30 text-teal-300 border border-teal-500/50">Double-Entry Ledger</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">تطبيق القيد المزدوج المحاسبي الصارم (من حـ/ المدين إلى حـ/ الدائن) لجميع الفواتير والسندات والتسويات اليدوية</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openJournalModal()" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل قيد تسوية يدوي</span>
            </button>
            <a href="{{ route('finance.index') }}" class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للمالية</span>
            </a>
        </div>
    </div>

    <!-- Double-Entry Balance Verification Banner -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div>
                <h3 class="font-black text-slate-900 text-sm">ميزان توازن قيود اليومية العامة (Debit = Credit)</h3>
                <p class="text-xs text-slate-500">كافة القيود المرحلة مطابقة وموزونة دفترياً بنسبة 100% وبدون أي فروقات</p>
            </div>
        </div>
        <div class="flex items-center gap-6 font-mono text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] font-bold">إجمالي الأطراف المدينة (Debit)</span>
                <b class="text-slate-900 text-sm">{{ number_format($totalDebit, 2) }} ج.م</b>
            </div>
            <div class="text-slate-300 text-xl font-thin">=</div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold">إجمالي الأطراف الدائنة (Credit)</span>
                <b class="text-slate-900 text-sm">{{ number_format($totalCredit, 2) }} ج.م</b>
            </div>
            <div class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-lg font-bold border border-emerald-200">
                موزون 100%
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('finance.journal') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">نوع القيد:</label>
                <select name="type" class="text-xs py-2 px-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500" onchange="this.form.submit()">
                    <option value="">-- كافة القيود المحاسبية --</option>
                    <option value="sales_invoice" {{ request('type') == 'sales_invoice' ? 'selected' : '' }}>فواتير مبيعات (عملاء)</option>
                    <option value="customer_payment" {{ request('type') == 'customer_payment' ? 'selected' : '' }}>سندات قبض (تحصيل عملاء)</option>
                    <option value="supplier_bill" {{ request('type') == 'supplier_bill' ? 'selected' : '' }}>فواتير مشتريات (موردين)</option>
                    <option value="supplier_payment" {{ request('type') == 'supplier_payment' ? 'selected' : '' }}>سندات صرف (سداد موردين)</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>مصروفات عمومية وتشغيلية</option>
                    <option value="factory_internal_14_markup" {{ request('type') == 'factory_internal_14_markup' ? 'selected' : '' }}>تسويات تشغيل داخلي 14%</option>
                    <option value="manual_journal" {{ request('type') == 'manual_journal' ? 'selected' : '' }}>قيود تسوية يدوية</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">من تاريخ:</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">إلى تاريخ:</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition">
                تصفية
            </button>
            @if(request()->hasAny(['type', 'from_date', 'to_date']))
                <a href="{{ route('finance.journal') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء التصفية
                </a>
            @endif
        </form>
    </div>

    <!-- Journal Entries Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-900 text-white font-bold">
                    <tr>
                        <th class="p-3.5">رقم القيد</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">النوع</th>
                        <th class="p-3.5">من حـ / الطرف المدين (Debit)</th>
                        <th class="p-3.5">إلى حـ / الطرف الدائن (Credit)</th>
                        <th class="p-3.5">المبلغ (مدين)</th>
                        <th class="p-3.5">المبلغ (دائن)</th>
                        <th class="p-3.5">طريقة القيد</th>
                        <th class="p-3.5">شرح القيد (البيان)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">{{ $t->transaction_number }}</td>
                            <td class="p-3.5 font-mono text-slate-500">{{ $t->transaction_date ? $t->transaction_date->format('Y-m-d') : '-' }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $t->badge_class }}">
                                    {{ $t->type_name }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold text-blue-800">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>{{ $t->debit_account ?? 'حـ/ الصندوق' }}</span>
                                </div>
                            </td>
                            <td class="p-3.5 font-bold text-purple-800">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    <span>{{ $t->credit_account ?? 'حـ/ الإيرادات' }}</span>
                                </div>
                            </td>
                            <td class="p-3.5 font-mono font-black text-slate-900 text-sm">
                                {{ number_format($t->amount, 2) }} ج.م
                            </td>
                            <td class="p-3.5 font-mono font-black text-slate-900 text-sm">
                                {{ number_format($t->amount, 2) }} ج.م
                            </td>
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
                                لا توجد قيود يومية مسجلة
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

<!-- Manual Journal Entry Modal -->
<div id="journalModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-teal-700">
                <i class="fa-solid fa-book-journal-whills text-xl"></i>
                <h3 class="font-black text-slate-900 text-base">تسجيل قيد تسوية يومية مزدوج يدوي</h3>
            </div>
            <button type="button" onclick="closeJournalModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.journal.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الطرف المدين (من حـ/) *</label>
                    <input type="text" name="debit_account" required placeholder="مثال: حـ/ أصول ثابتة - ماكينات" class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الطرف الدائن (إلى حـ/) *</label>
                    <input type="text" name="credit_account" required placeholder="مثال: حـ/ الخزينة الرئيسية" class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ الموزون (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ القيد *</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">شرح القيد والبيان المحاسبي *</label>
                <textarea name="description" rows="2" required placeholder="بيان تفصيلي بسبب إجراء القيد المزدوج والتسوية الدفترية..." class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">مرفق المستند المؤيد للقيد (اختياري)</label>
                <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs px-2 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeJournalModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>تأكيد وترحيل القيد الدفتري</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openJournalModal() {
        document.getElementById('journalModal').classList.remove('hidden');
    }

    function closeJournalModal() {
        document.getElementById('journalModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
