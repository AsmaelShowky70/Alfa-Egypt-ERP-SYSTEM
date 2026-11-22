@extends('layouts.app')

@section('title', 'تسجيل حركة مالية جديدة - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">تسجيل قيد / حركة مالية جديدة</h1>
            <p class="text-sm text-slate-500 mt-1">تسجيل المقبوضات والمصروفات والتكاليف الثابتة والمتغيرة وهوامش التصنيع الداخلي</p>
        </div>
        <a href="{{ route('finance.index') }}" class="btn-secondary flex items-center gap-2">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة للإدارة المالية</span>
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('finance.transactions.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            @csrf

            <div class="p-6 sm:p-8 space-y-6">
                @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">نوع الحركة المالية <span class="text-rose-500">*</span></label>
                        <select name="type" required class="input-field">
                            <option value="customer_payment">متحصلات وإيرادات عملاء (Customer Payment)</option>
                            <option value="fixed_cost">تكاليف ثابتة - إيجارات، رواتب، إهلاكات (Fixed Cost)</option>
                            <option value="variable_cost">تكاليف متغيرة - خامات، نقل، طاقة، تصنيع عند الغير (Variable Cost)</option>
                            <option value="factory_internal_14_markup">هامش ربح تصنيع داخلي 14% لمصنع مادة خام</option>
                            <option value="supplier_payment">سداد مورد / مصنع خارجي (Supplier Payment)</option>
                            <option value="maintenance">مصاريف صيانة وقطع غيار (Maintenance)</option>
                            <option value="other">مصروفات إدارية وعمومية أخرى (Other)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">تصنيف البند المحاسبي <span class="text-rose-500">*</span></label>
                        <input type="text" name="category" placeholder="مثال: خامات كرتون، كهرباء، إيجار، سولار، راتب" required class="input-field" value="{{ old('category') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">المبلغ (ج.م) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required class="input-field pl-12 font-mono text-lg font-bold" value="{{ old('amount') }}">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold text-sm">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">تاريخ المعاملة <span class="text-rose-500">*</span></label>
                        <input type="date" name="transaction_date" required class="input-field font-mono" value="{{ old('transaction_date', date('Y-m-d')) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">طريقة الدفع / التحصيل <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="input-field">
                            <option value="bank_transfer">تحويل بنكي / شيك (Bank Transfer / Cheque)</option>
                            <option value="cash">نقداً - خزينة الشركة (Cash)</option>
                            <option value="internal_ledger">قيد تسوية ومقاصة داخلية (Internal Ledger)</option>
                            <option value="instapay">تحصيل إلكتروني / انستاباي (InstaPay)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">تحميل التكلفة / الإيراد على خط إنتاج / مصنع</label>
                        <select name="factory_id" class="input-field">
                            <option value="">-- عام على مستوى الشركة (General) --</option>
                            @foreach($factories as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->code }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">يحدد المصنع لحساب قائمة الأرباح والخسائر المستقلة لكل خط إنتاج</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">ربط بأمر مبيعات (اختياري)</label>
                        <select name="sales_order_id" class="input-field">
                            <option value="">-- بدون ربط بأمر بيع --</option>
                            @foreach($salesOrders as $so)
                                <option value="{{ $so->id }}">{{ $so->order_number }} - {{ $so->customer?->name ?? 'عميل' }} ({{ number_format($so->total_price, 2) }} ج.م)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">ربط بأمر شراء / توريد (اختياري)</label>
                        <select name="purchase_order_id" class="input-field">
                            <option value="">-- بدون ربط بأمر توريد --</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier?->name ?? 'داخلي' }} ({{ number_format($po->total_amount, 2) }} ج.م)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">الوصف والبيان المحاسبي <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="3" required placeholder="شرح تفصيلي للحركة المالية أو رقم الفاتورة الورقية المرفقة..." class="input-field">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">صورة إيصال التحصيل أو الفاتورة الورقية (مرفق)</label>
                        <input type="file" name="attachment" accept="image/jpeg,image/png,image/jpg,application/pdf" class="file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-slate-300 rounded-xl w-full text-sm text-slate-500">
                        <p class="text-xs text-slate-500 mt-1">يتم أرشفة الإيصال في النظام للرجوع إليه عند المراجعة والتدقيق المالي (Max 4MB)</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-3">
                <a href="{{ route('finance.index') }}" class="btn-secondary">إلغاء</a>
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <i class="fa-solid fa-save"></i>
                    <span>حفظ وترحيل القيد المالي</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
