@extends('layouts.app')

@section('title', 'المالية والحسابات ومراكز التكلفة')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-coins text-teal-600"></i>
                <span>الإدارة المالية والتكاليف ومراكز أرباح المصانع</span>
            </h1>
            <p class="text-xs text-slate-500">حصر الفواتير، التكاليف الثابتة والمتغيرة، هوامش الـ 14% المحملة للخطوط الداخلية، وشاشة إنذار الخسائر</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.transactions.create') }}" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل قيد مالي / فاتورة</span>
            </a>
            <a href="{{ route('finance.pricing') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-tags"></i>
                <span>تسعير المنتجات</span>
            </a>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-arrow-down-long"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي المقبوضات</p>
                <h3 class="text-xl font-black text-slate-900">{{ number_format($totalRevenue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">التكاليف الثابتة (رواتب/إيجار/مرافق)</p>
                <h3 class="text-xl font-black text-rose-700">{{ number_format($fixedCosts, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">هوامش أرباح الخطوط (14% داخلي)</p>
                <h3 class="text-xl font-black text-indigo-700">{{ number_format($markup14Total, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">صافي الأرباح التشغيلية التقديرية</p>
                <h3 class="text-xl font-black {{ $netProfit >= 0 ? 'text-teal-700' : 'text-rose-700' }}">
                    {{ number_format($netProfit, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span>
                </h3>
            </div>
        </div>
    </div>

    <!-- Factory Profit & Loss Performance Matrix (مراكز أرباح المصانع الـ 6) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-industry text-indigo-600"></i>
                    <span>قائمة الأرباح والخسائر لكل مصنع وخط إنتاج مستقل (مراكز ربح 14%)</span>
                </h2>
                <p class="text-xs text-slate-500">تتبع ربحية كل خط لمعرفة نقاط القوة والضعف بالشركة بدقة</p>
            </div>
            <a href="{{ route('factories.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">عرض الشاشة الكاملة</a>
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
                            <span>إجمالي التوريدات (مع الـ 14%):</span>
                            <b class="text-slate-900 font-mono">{{ number_format($item['revenue'], 2) }} ج.م</b>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>التكاليف المباشرة المسجلة:</span>
                            <b class="text-rose-700 font-mono">{{ number_format($item['cost'], 2) }} ج.م</b>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200 font-bold">
                            <span class="text-slate-700">صافي مساهمة الخط:</span>
                            <span class="font-black text-sm {{ $item['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ number_format($item['profit'], 2) }} ج.م
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Financial Transactions Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-numeric text-teal-600"></i>
                <span>دفتر اليومية والقيود المالية والمصروفات</span>
            </h2>
            <a href="{{ route('finance.transactions.create') }}" class="text-xs font-bold text-teal-600 hover:underline">+ تسجيل قيد جديد</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم القيد</th>
                        <th class="py-3 px-4">التصنيف</th>
                        <th class="py-3 px-4">البيان والشرح</th>
                        <th class="py-3 px-4">طريقة السداد</th>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">المصنع</th>
                        <th class="py-3 px-4">المرفق / الفاتورة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $t->transaction_number }}</td>
                            <td class="py-3 px-4 font-bold text-teal-900">{{ $t->category }}</td>
                            <td class="py-3 px-4 text-slate-700 max-w-xs truncate">{{ $t->description }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $t->payment_method }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $t->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-black {{ str_contains($t->type, 'payment') || str_contains($t->type, 'markup') ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ number_format($t->amount, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $t->factory->name ?? 'مركزي' }}</td>
                            <td class="py-3 px-4">
                                @if($t->attachment)
                                    <a href="{{ asset($t->attachment) }}" target="_blank" class="text-blue-600 hover:underline font-bold text-[11px] flex items-center gap-1">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>عرض الفاتورة</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">بدون مرفق</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">لا توجد معاملات مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
