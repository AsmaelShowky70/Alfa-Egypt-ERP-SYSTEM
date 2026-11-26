@extends('layouts.app')

@section('title', 'المصروفات العمومية والتشغيلية (م غمومية)')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-l from-slate-900 to-black p-6 rounded-3xl text-white shadow-xl border-b-4 border-rose-600">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-600/20 border border-rose-500/40 flex items-center justify-center text-rose-400 text-2xl">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>المصروفات العمومية والإدارية والتشغيلية (م غمومية)</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-rose-500/30 text-rose-300 border border-rose-500/50">18 بنداً محاسبياً</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">تبويب ومطابقة المصروفات من واقع كشف إكسيل تحليل أكتوبر (إجمالي 644,073 ج.م) والتحميل على مراكز التكلفة</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openExpenseModal()" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل مصروف جديد</span>
            </button>
            <a href="{{ route('finance.index') }}" class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للمالية</span>
            </a>
        </div>
    </div>

    <!-- Grand Total Highlight Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المصروفات العمومية والتشغيلية المسجلة</span>
            <h2 class="text-3xl font-black text-rose-700">
                {{ number_format($grandTotal, 2) }}
                <span class="text-sm font-bold text-slate-500">ج.م</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">موزعة عبر 18 بنداً تشغيلياً معتمدة من الإدارة المالية لشركة الفا ايجبت</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.analysis.october') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black rounded-xl text-xs font-black shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-table"></i>
                <span>مقارنة بشيت إكسيل أكتوبر (644K ج.م)</span>
            </a>
        </div>
    </div>

    <!-- 18 Expense Categories Breakdown Grid -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-rose-600"></i>
            <span>توزيع المصروفات حسب البنود الـ 18 (م غمومية)</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($categoryBreakdown as $cat)
                @php
                    $pct = $grandTotal > 0 ? ($cat->total_amount / $grandTotal) * 100 : 0;
                @endphp
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-rose-50/50 hover:border-rose-300 transition space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                        <span class="truncate" title="{{ $cat->category }}">{{ $cat->category }}</span>
                        <span class="text-[11px] font-black text-rose-700 font-mono">{{ number_format($pct, 1) }}%</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ number_format($cat->total_amount, 2) }} <span class="text-[10px] font-normal text-slate-500">ج.م</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold font-mono">{{ $cat->total_count }} سند</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-rose-600 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Expenses Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-list-check text-rose-600"></i>
                <span>دفتر قيد المصروفات وسندات الصرف الفردية</span>
            </h3>

            <!-- Filters -->
            <form method="GET" action="{{ route('finance.expenses') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <select name="category" class="text-xs py-2 px-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500" onchange="this.form.submit()">
                    <option value="">-- كافة بنود المصروفات --</option>
                    @foreach($availableCategories as $ac)
                        <option value="{{ $ac }}" {{ request('category') == $ac ? 'selected' : '' }}>{{ $ac }}</option>
                    @endforeach
                </select>
                @if(request()->filled('category'))
                    <a href="{{ route('finance.expenses') }}" class="px-2.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">
                        إلغاء
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">رقم السند</th>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">بند المصروف</th>
                        <th class="p-3.5">المصنع / مركز التكلفة</th>
                        <th class="p-3.5">المبلغ</th>
                        <th class="p-3.5">طريقة الدفع</th>
                        <th class="p-3.5">الطرف الدائن</th>
                        <th class="p-3.5">البيان والتفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">{{ $e->transaction_number }}</td>
                            <td class="p-3.5 font-mono text-slate-500">{{ $e->transaction_date ? $e->transaction_date->format('Y-m-d') : '-' }}</td>
                            <td class="p-3.5 font-bold text-rose-700">{{ $e->category }}</td>
                            <td class="p-3.5">
                                @if($e->factory)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                        {{ $e->factory->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">إدارة مركزية</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono font-black text-rose-700 text-sm">
                                {{ number_format($e->amount, 2) }} ج.م
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-bold">
                                    {{ $e->payment_method }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $e->credit_account ?? 'حـ/ الخزينة' }}</td>
                            <td class="p-3.5 text-slate-600 max-w-xs truncate" title="{{ $e->description }}">{{ $e->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                لا توجد سندات مصروفات مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Record Expense Modal -->
<div id="expenseModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-rose-700">
                <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                <h3 class="font-black text-slate-900 text-base">تسجيل سند صرف مصروف جديد</h3>
            </div>
            <button type="button" onclick="closeExpenseModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">بند المصروف (من واقع الـ 18 بنداً) *</label>
                <select name="category" required class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500">
                    @foreach($availableCategories as $ac)
                        <option value="{{ $ac }}">{{ $ac }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">قيمة المصروف (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الصرف *</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الصرف *</label>
                    <select name="payment_method" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500">
                        <option value="نقدي">نقدي (خصم من الخزينة الرئيسية)</option>
                        <option value="شيك">شيك بنكي</option>
                        <option value="تحويل بنكي">تحويل بنكي</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المصنع / مركز التكلفة</label>
                    <select name="factory_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500">
                        <option value="">-- إدارة مركزية عامة --</option>
                        @foreach($factories as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">البيان / تفاصيل الفاتورة *</label>
                <textarea name="description" rows="2" required placeholder="بيان المصروف وتفاصيل الإيصال..." class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">مرفق الفاتورة / إيصال الصرف</label>
                <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs px-2 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إلغاء
                </button>
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>ترحيل المصروف للخزينة والقوائم</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openExpenseModal() {
        document.getElementById('expenseModal').classList.remove('hidden');
    }

    function closeExpenseModal() {
        document.getElementById('expenseModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
