@extends('layouts.app')

@section('title', 'تسجيل طلبية مبيعات جديدة')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">تسجيل طلبية مبيعات جديدة لعميل</h1>
            <p class="text-xs text-slate-500">سيتم إحالة الطلب تلقائياً إلى إدارة التشغيل لفحص الخامات بالمخزن وتحديد موعد العمل</p>
        </div>
        <a href="{{ route('sales.orders') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لطلبات المبيعات
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('sales.orders.store') }}" method="POST" id="orderForm" class="space-y-6">
            @csrf

            <!-- Client & Date Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اختر العميل / المطعم *</label>
                    <select name="customer_id" id="customerSelect" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                        <option value="">-- حدد العميل --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ استلام الاتصال والطلب *</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">التاريخ المستهدف المطلوب من العميل</label>
                    <input type="date" name="target_delivery_date" value="{{ \Carbon\Carbon::now()->addDays(5)->format('Y-m-d') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>
            </div>

            <!-- Product and Items Selection -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-slate-50/60 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="text-xs font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-box text-brand-600"></i>
                        <span>المنتج المطلوب والكمية والتسعير</span>
                    </span>
                    <span class="text-[11px] text-slate-500">التكلفة تحسب تلقائياً من واقع مكونات الـ BOM</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <!-- Product -->
                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">المنتج (الكوڤير المخصص)</label>
                        <select name="items[0][product_id]" id="productSelect" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-brand-600 outline-none">
                            <option value="">-- اختر طقم الكوڤير أو المنتج --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" data-price="{{ $p->selling_price }}">
                                    {{ $p->name }} [تكلفة: {{ number_format($p->cost_price, 2) }} ج.م]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">الكمية المطلوبة (قطعة)</label>
                        <input type="number" name="items[0][quantity]" id="itemQty" value="50000" min="1" step="1" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-brand-600 outline-none">
                    </div>

                    <!-- Unit Price -->
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">سعر بيع القطعة للعميل (ج.م)</label>
                        <input type="number" step="0.01" name="items[0][unit_price]" id="itemPrice" value="2.15" min="0" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-brand-700 focus:ring-1 focus:ring-brand-600 outline-none">
                    </div>
                </div>

                <!-- Real-time Cost & Margin Preview Box -->
                <div id="costPreviewBox" class="p-3.5 rounded-xl border border-slate-200 bg-white text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">تكلفة الخامات المباشرة للقطعة (BOM): <b id="lblCost" class="text-slate-800">0.00 ج.م</b></span>
                        <span class="text-slate-500">سعر البيع للقطعة: <b id="lblPrice" class="text-brand-700">0.00 ج.م</b></span>
                        <span class="font-bold">هامش الربح المتوقع: <b id="lblMargin" class="text-emerald-700">0%</b></span>
                    </div>

                    <!-- Loss Warning (Alarm) Banner if Selling Price < Cost -->
                    <div id="lossAlertBanner" class="hidden p-3 bg-rose-50 border border-rose-300 rounded-xl text-rose-900 space-y-1">
                        <div class="flex items-center gap-2 font-black text-xs text-rose-700">
                            <i class="fa-solid fa-triangle-exclamation animate-bounce"></i>
                            <span>تنبيه نظام ألفا إيجبت: سعر البيع للعميل أقل من تكلفة الخامات المباشرة!</span>
                        </div>
                        <p class="text-[11px] text-rose-800">
                            سيتم حفظ الطلب وفقاً للسعر التعاقدي الثابت المتفق عليه مع العميل، ولكن سيطلق النظام <b>جرس إنذار (Loss Alarm)</b> فوراً لإدارة الحسابات والإدارة العليا لاتخاذ إجراءات إعادة التفاوض.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">ملاحظات التخصيص والطباعة للطلب</label>
                    <input type="text" name="items[0][notes]" placeholder="مثال: لوجو أبو طارق أزرق داكن، معلقة إنجليزي، خلة صيني مغلفة..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-brand-600 outline-none">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">تعليمات وملاحظات خاصة بالعميل أو التوصيل</label>
                <textarea name="client_notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="مثال: مطلوب تسليم الشحنة لمخزن شامبليون بعد الساعة 10 صباحاً..."></textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('sales.orders') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>تسجيل الطلب وإحالته لإدارة التشغيل</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const productSelect = document.getElementById('productSelect');
        const itemPrice = document.getElementById('itemPrice');
        const lblCost = document.getElementById('lblCost');
        const lblPrice = document.getElementById('lblPrice');
        const lblMargin = document.getElementById('lblMargin');
        const lossAlertBanner = document.getElementById('lossAlertBanner');

        function updateCalculations() {
            const selectedOpt = productSelect.options[productSelect.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) return;

            const cost = parseFloat(selectedOpt.getAttribute('data-cost')) || 0;
            const price = parseFloat(itemPrice.value) || 0;

            lblCost.textContent = cost.toFixed(2) + ' ج.م';
            lblPrice.textContent = price.toFixed(2) + ' ج.م';

            if (price > 0) {
                const margin = (((price - cost) / price) * 100).toFixed(1);
                lblMargin.textContent = margin + '%';
                if (price < cost) {
                    lblMargin.className = 'text-rose-600 font-black';
                    lossAlertBanner.classList.remove('hidden');
                } else {
                    lblMargin.className = 'text-emerald-700 font-black';
                    lossAlertBanner.classList.add('hidden');
                }
            }
        }

        productSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.getAttribute('data-price')) {
                itemPrice.value = parseFloat(opt.getAttribute('data-price')).toFixed(2);
            }
            updateCalculations();
        });

        itemPrice.addEventListener('input', updateCalculations);

        // Run initially if preselected
        if (productSelect.selectedIndex > 0) {
            updateCalculations();
        }
    });
</script>
@endpush
