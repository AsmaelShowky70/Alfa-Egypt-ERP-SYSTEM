@extends('layouts.app')

@section('title', 'تسجيل طلبية مبيعات جديدة (أصناف متعددة للعميل)')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header & Back Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cart-flatbed text-brand-600"></i>
                <span>تسجيل طلبية مبيعات جديدة لعميل</span>
            </h1>
            <p class="text-xs text-slate-500">إضافة طلب كامل يشمل كافة أصناف العميل (أطقم الكوڤير، 1*1، 2*1، ومنتجات أخرى) مع احتساب فوري للتكاليف وهامش الربح</p>
        </div>
        <a href="{{ route('sales.orders') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع لطلبات المبيعات</span>
        </a>
    </div>

    <!-- Main Order Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('sales.orders.store') }}" method="POST" id="orderForm" class="space-y-6">
            @csrf

            <!-- Section 1: Client & Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 p-5 bg-slate-50/80 rounded-2xl border border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اختر العميل / المطعم *</label>
                    <select name="customer_id" id="customerSelect" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-600 outline-none transition">
                        <option value="">-- حدد العميل المستهدف --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (request('customer_id') == $c->id || (old('customer_id') == $c->id)) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ استلام الاتصال والطلب *</label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-600 outline-none transition font-sans">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">التاريخ المستهدف المطلوب من العميل</label>
                    <input type="date" name="target_delivery_date" value="{{ old('target_delivery_date', \Carbon\Carbon::now()->addDays(5)->format('Y-m-d')) }}" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-600 outline-none transition font-sans">
                </div>
            </div>

            <!-- Quick Auto-Fill Toolbar for Specific Customer (e.g. Abu Tarek) -->
            <div id="quickCustomerToolbar" class="hidden p-4 rounded-2xl bg-amber-50/80 border border-amber-200/90 text-amber-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-bolt"></i>
                    </span>
                    <div>
                        <div class="font-black text-xs text-amber-900" id="quickCustomerTitle">تشكيلة أصناف العميل المعتمدة</div>
                        <div class="text-[11px] text-amber-800/80">يمكنك ملء الطلبية بنقرة واحدة بكافة أصناف العميل المخصصة لتوفير الوقت</div>
                    </div>
                </div>
                <button type="button" id="btnQuickFillCustomer" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 self-end sm:self-auto">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة كافة أصناف العميل دفعة واحدة</span>
                </button>
            </div>

            <!-- Section 2: Order Items Repeater (الأصناف والكميات والتسعير) -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-2 border-b border-slate-200 gap-2">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-brand-600"></i>
                            <span>أصناف الطلبية والكميات والتسعير التعاقدي</span>
                        </h2>
                        <p class="text-xs text-slate-500">أضف عدداً غير محدود من المنتجات في نفس الأوردر (أطقم الكوڤير، معالق مفردة، مناديل، جوانتيات، وايبس)</p>
                    </div>

                    <button type="button" id="btnAddItem" class="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-circle-plus text-sm"></i>
                        <span>+ إضافة صنف آخر للأوردر</span>
                    </button>
                </div>

                <!-- Dynamic Items Container -->
                <div id="itemsContainer" class="space-y-4">
                    <!-- Populated dynamically via JavaScript -->
                </div>
            </div>

            <!-- Section 3: Live Order Totals & KPI Summary Box -->
            <div class="p-5 rounded-2xl bg-slate-900 text-white space-y-4 shadow-lg border border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-brand-400"></i>
                        <span class="font-extrabold text-xs">ملخص التكاليف والمبيعات المباشرة للطلبية بالكامل</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono" id="summaryItemsCount">0 أصناف</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block text-[11px] mb-1">إجمالي الكمية المطلوبة:</span>
                        <div class="text-base font-black text-white font-mono" id="sumTotalPieces">0 <span class="text-xs font-sans text-slate-400">قطعة</span></div>
                    </div>

                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block text-[11px] mb-1">إجمالي تكلفة الخامات (BOM):</span>
                        <div class="text-base font-black text-slate-300 font-mono" id="sumTotalCost">0.00 <span class="text-xs font-sans text-slate-400">ج.م</span></div>
                    </div>

                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block text-[11px] mb-1">إجمالي المبيعات (قبل الضريبة):</span>
                        <div class="text-base font-black text-brand-400 font-mono" id="sumSubtotal">0.00 <span class="text-xs font-sans text-slate-400">ج.م</span></div>
                    </div>

                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block text-[11px] mb-1">هامش الربح الإجمالي:</span>
                        <div class="text-base font-black font-mono" id="sumProfitMargin">0.0%</div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-0.5">
                        <div class="text-slate-400 text-[11px]">ضريبة القيمة المضافة (14%): <b class="text-slate-200 font-mono" id="sumTax">0.00 ج.م</b></div>
                        <div class="text-slate-300 font-bold">المبلغ الإجمالي النهائي للطلبية: <b class="text-emerald-400 text-sm font-mono" id="sumGrandTotal">0.00 ج.م</b></div>
                    </div>

                    <!-- Loss Warning Banner if Selling Price < Cost -->
                    <div id="lossAlertBanner" class="hidden p-3 bg-rose-950/80 border border-rose-500 rounded-xl text-rose-200 space-y-1 w-full sm:w-auto max-w-lg">
                        <div class="flex items-center gap-2 font-black text-xs text-rose-400">
                            <i class="fa-solid fa-triangle-exclamation animate-bounce"></i>
                            <span>تنبيه نظام ألفا إيجبت: يوجد صنف أو أكثر يباع بسعر أقل من تكلفة الخامات المباشرة!</span>
                        </div>
                        <p class="text-[11px] text-rose-300 leading-relaxed">
                            سيتم حفظ الطلب وفقاً للاتفاق المعتمد، ولكن سيطلق النظام <b>جرس إنذار (Loss Alarm)</b> فوري للإدارة المالية والتكاليف لاتخاذ التدابير اللازمة.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Client & Delivery Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">تعليمات وملاحظات خاصة بالعميل أو التوصيل</label>
                <textarea name="client_notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="مثال: تسليم الشحنة لمخزن العميل بشامبليون، التعبئة في كراتين 500 قطعة، مواعيد الاستلام صباحاً..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('sales.orders') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                    إلغاء
                </a>
                <button type="submit" class="px-7 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-black shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>تسجيل الطلبية بالكامل وإحالتها لإدارة التشغيل</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const productsData = @json($productsJson);

    const customersData = @json($customersJson);

    document.addEventListener("DOMContentLoaded", function () {
        const customerSelect = document.getElementById('customerSelect');
        const itemsContainer = document.getElementById('itemsContainer');
        const btnAddItem = document.getElementById('btnAddItem');
        const quickCustomerToolbar = document.getElementById('quickCustomerToolbar');
        const quickCustomerTitle = document.getElementById('quickCustomerTitle');
        const btnQuickFillCustomer = document.getElementById('btnQuickFillCustomer');

        // Summary elements
        const summaryItemsCount = document.getElementById('summaryItemsCount');
        const sumTotalPieces = document.getElementById('sumTotalPieces');
        const sumTotalCost = document.getElementById('sumTotalCost');
        const sumSubtotal = document.getElementById('sumSubtotal');
        const sumProfitMargin = document.getElementById('sumProfitMargin');
        const sumTax = document.getElementById('sumTax');
        const sumGrandTotal = document.getElementById('sumGrandTotal');
        const lossAlertBanner = document.getElementById('lossAlertBanner');

        let itemCounter = 0;

        // Render Product options grouped nicely
        function buildProductOptions(selectedProductId, selectedCustomerId) {
            let html = '<option value="">-- اختر طقم الكوڤير أو المنتج --</option>';

            const customerProds = productsData.filter(p => selectedCustomerId && p.customer_id == selectedCustomerId);
            const standardProds = productsData.filter(p => !p.customer_id);
            const otherProds = productsData.filter(p => p.customer_id && (!selectedCustomerId || p.customer_id != selectedCustomerId));

            if (customerProds.length > 0) {
                html += '<optgroup label="⭐ منتجات العميل المخصصة المعتمدة">';
                customerProds.forEach(p => {
                    const sel = (selectedProductId && p.id == selectedProductId) ? 'selected' : '';
                    html += `<option value="${p.id}" ${sel}>${p.name} [تكلفة: ${p.cost_price.toFixed(2)} ج.م]</option>`;
                });
                html += '</optgroup>';
            }

            if (standardProds.length > 0) {
                html += '<optgroup label="📦 منتجات عامة وقياسية (المعالق، المناديل، الأطقم الاقتصادية، الجوانتيات، الوايبس)">';
                standardProds.forEach(p => {
                    const sel = (selectedProductId && p.id == selectedProductId) ? 'selected' : '';
                    html += `<option value="${p.id}" ${sel}>${p.name} [تكلفة: ${p.cost_price.toFixed(2)} ج.م]</option>`;
                });
                html += '</optgroup>';
            }

            if (otherProds.length > 0) {
                html += '<optgroup label="🏭 منتجات خطوط العملاء الأخرى">';
                otherProds.forEach(p => {
                    const sel = (selectedProductId && p.id == selectedProductId) ? 'selected' : '';
                    html += `<option value="${p.id}" ${sel}>${p.name} [تكلفة: ${p.cost_price.toFixed(2)} ج.م]</option>`;
                });
                html += '</optgroup>';
            }

            return html;
        }

        // Add a line item card
        function addItem(productId = '', quantity = 50000, unitPrice = null, notes = '') {
            const index = itemCounter++;
            const selectedCustId = customerSelect.value;

            // Product defaults
            let defaultPrice = unitPrice;
            if (productId && (defaultPrice === null || defaultPrice === undefined)) {
                const pr = productsData.find(p => p.id == productId);
                if (pr) defaultPrice = pr.selling_price;
            }
            if (defaultPrice === null || defaultPrice === undefined) {
                defaultPrice = 2.15;
            }

            const card = document.createElement('div');
            card.className = 'item-row bg-slate-50/70 hover:bg-slate-50 rounded-2xl p-4 sm:p-5 border border-slate-200 transition space-y-3';
            card.id = `item-row-${index}`;
            card.dataset.index = index;

            card.innerHTML = `
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs item-number font-mono">
                            #
                        </span>
                        <span class="text-xs font-black text-slate-800">بيانات الصنف</span>
                        <span class="item-bom-badge text-[11px] text-slate-500"></span>
                    </div>

                    <button type="button" class="btn-remove-item text-xs font-bold text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1 rounded-lg transition flex items-center gap-1" title="حذف هذا الصنف">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>حذف الصنف</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Product Dropdown -->
                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">المنتج (طقم الكوڤير أو الصنف) *</label>
                        <select name="items[${index}][product_id]" required class="item-product-select w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-1 focus:ring-brand-600 outline-none">
                            ${buildProductOptions(productId, selectedCustId)}
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">الكمية المطلوبة (قطعة) *</label>
                        <input type="number" name="items[${index}][quantity]" value="${quantity}" min="1" step="1" required class="item-qty-input w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-brand-600 outline-none font-mono">
                    </div>

                    <!-- Unit Price -->
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">سعر بيع القطعة للعميل (ج.م) *</label>
                        <input type="number" step="0.01" name="items[${index}][unit_price]" value="${parseFloat(defaultPrice).toFixed(2)}" min="0" required class="item-price-input w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-brand-700 focus:ring-1 focus:ring-brand-600 outline-none font-mono">
                    </div>
                </div>

                <!-- BOM Pills & Financial Calculation Banner for this line item -->
                <div class="p-3 bg-white rounded-xl border border-slate-200/90 text-xs flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-slate-500">تكلفة خامات القطعة (BOM): <b class="item-lbl-cost text-slate-900 font-mono">0.00 ج.م</b></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-500">إجمالي البيع: <b class="item-lbl-subtotal text-slate-900 font-mono">0.00 ج.م</b></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-500">إجمالي التكلفة: <b class="item-lbl-totalcost text-slate-600 font-mono">0.00 ج.م</b></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="item-margin-badge px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-slate-100 text-slate-700">
                            هامش الربح: 0%
                        </span>
                    </div>
                </div>

                <!-- BOM Components list preview -->
                <div class="item-bom-preview hidden p-2 bg-slate-50 rounded-lg border border-slate-200/60 text-[11px] text-slate-600 flex flex-wrap gap-1.5 items-center">
                    <span class="font-bold text-slate-400">مكونات الـ BOM:</span>
                    <span class="item-bom-components-tags flex flex-wrap gap-1"></span>
                </div>

                <!-- Notes / Customization -->
                <div>
                    <input type="text" name="items[${index}][notes]" value="${notes}" placeholder="ملاحظات التخصيص أو الطباعة لهذا الصنف (مثال: طباعة لوجو، خلة صيني، سيلوفان أزرق)..." class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-brand-600 outline-none">
                </div>
            `;

            itemsContainer.appendChild(card);

            // Bind Events on newly added card
            const productSelect = card.querySelector('.item-product-select');
            const qtyInput = card.querySelector('.item-qty-input');
            const priceInput = card.querySelector('.item-price-input');
            const btnRemove = card.querySelector('.btn-remove-item');

            productSelect.addEventListener('change', function () {
                const prod = productsData.find(p => p.id == this.value);
                if (prod) {
                    priceInput.value = prod.selling_price.toFixed(2);
                }
                updateCardCalculations(card);
                updateOrderTotals();
            });

            qtyInput.addEventListener('input', function () {
                updateCardCalculations(card);
                updateOrderTotals();
            });

            priceInput.addEventListener('input', function () {
                updateCardCalculations(card);
                updateOrderTotals();
            });

            btnRemove.addEventListener('click', function () {
                const allRows = itemsContainer.querySelectorAll('.item-row');
                if (allRows.length <= 1) {
                    alert('يجب الإبقاء على صنف واحد على الأقل في الطلبية.');
                    return;
                }
                card.remove();
                renumberItems();
                updateOrderTotals();
            });

            // Initial calculation
            updateCardCalculations(card);
            renumberItems();
            updateOrderTotals();
        }

        // Update single card calculations
        function updateCardCalculations(card) {
            const productSelect = card.querySelector('.item-product-select');
            const qtyInput = card.querySelector('.item-qty-input');
            const priceInput = card.querySelector('.item-price-input');

            const lblCost = card.querySelector('.item-lbl-cost');
            const lblSubtotal = card.querySelector('.item-lbl-subtotal');
            const lblTotalCost = card.querySelector('.item-lbl-totalcost');
            const marginBadge = card.querySelector('.item-margin-badge');
            const bomPreview = card.querySelector('.item-bom-preview');
            const bomTags = card.querySelector('.item-bom-components-tags');

            const prodId = productSelect.value;
            const prod = productsData.find(p => p.id == prodId);

            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const cost = prod ? prod.cost_price : 0;

            const subtotal = qty * price;
            const totalCost = qty * cost;

            lblCost.textContent = cost.toFixed(2) + ' ج.م';
            lblSubtotal.textContent = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م';
            lblTotalCost.textContent = totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م';

            if (prod && prod.boms && prod.boms.length > 0) {
                bomPreview.classList.remove('hidden');
                bomTags.innerHTML = prod.boms.map(b => `<span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">${b.name}</span>`).join('');
            } else {
                bomPreview.classList.add('hidden');
            }

            if (price > 0 && cost > 0) {
                const margin = (((price - cost) / price) * 100).toFixed(1);
                if (price < cost) {
                    marginBadge.className = 'item-margin-badge px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-rose-100 text-rose-800 border border-rose-300';
                    marginBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i> خسارة: ${margin}% (${(cost - price).toFixed(2)} ج.م/ق)`;
                } else {
                    marginBadge.className = 'item-margin-badge px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800 border border-emerald-300';
                    marginBadge.innerHTML = `<i class="fa-solid fa-check text-emerald-600 mr-1"></i> ربح: ${margin}% (+${(price - cost).toFixed(2)} ج.م/ق)`;
                }
            } else {
                marginBadge.className = 'item-margin-badge px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-slate-100 text-slate-600';
                marginBadge.textContent = 'هامش الربح: 0%';
            }
        }

        // Renumber item rows sequentially
        function renumberItems() {
            const rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach((row, i) => {
                const num = row.querySelector('.item-number');
                if (num) num.textContent = (i + 1);
            });
            summaryItemsCount.textContent = `${rows.length} أصناف في الأوردر`;
        }

        // Update Overall Order Totals
        function updateOrderTotals() {
            const rows = itemsContainer.querySelectorAll('.item-row');
            let totalPieces = 0;
            let totalCost = 0;
            let subtotal = 0;
            let hasAnyLoss = false;

            rows.forEach(row => {
                const productSelect = row.querySelector('.item-product-select');
                const qtyInput = row.querySelector('.item-qty-input');
                const priceInput = row.querySelector('.item-price-input');

                const prodId = productSelect.value;
                const prod = productsData.find(p => p.id == prodId);

                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const cost = prod ? prod.cost_price : 0;

                totalPieces += qty;
                subtotal += (qty * price);
                totalCost += (qty * cost);

                if (price > 0 && cost > 0 && price < cost) {
                    hasAnyLoss = true;
                }
            });

            const tax = subtotal * 0.14;
            const grandTotal = subtotal + tax;

            let margin = 0;
            if (subtotal > 0) {
                margin = (((subtotal - totalCost) / subtotal) * 100).toFixed(1);
            }

            sumTotalPieces.innerHTML = `${totalPieces.toLocaleString('en-US')} <span class="text-xs font-sans text-slate-400">قطعة</span>`;
            sumTotalCost.innerHTML = `${totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span class="text-xs font-sans text-slate-400">ج.م</span>`;
            sumSubtotal.innerHTML = `${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span class="text-xs font-sans text-slate-400">ج.م</span>`;
            sumTax.textContent = `${tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ج.م`;
            sumGrandTotal.textContent = `${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ج.م`;

            if (subtotal > totalCost) {
                sumProfitMargin.className = 'text-base font-black font-mono text-emerald-400';
                sumProfitMargin.textContent = `+${margin}%`;
            } else if (subtotal > 0 && subtotal < totalCost) {
                sumProfitMargin.className = 'text-base font-black font-mono text-rose-400';
                sumProfitMargin.textContent = `${margin}%`;
            } else {
                sumProfitMargin.className = 'text-base font-black font-mono text-slate-300';
                sumProfitMargin.textContent = '0.0%';
            }

            if (hasAnyLoss || (subtotal > 0 && subtotal < totalCost)) {
                lossAlertBanner.classList.remove('hidden');
            } else {
                lossAlertBanner.classList.add('hidden');
            }
        }

        // On Customer Selection Change
        function handleCustomerChange() {
            const custId = customerSelect.value;
            const cust = customersData.find(c => c.id == custId);

            if (cust) {
                // Check if customer has products or is Abu Tarek
                const custProds = productsData.filter(p => p.customer_id == custId);
                const hasCustomProducts = custProds.length > 0;

                quickCustomerToolbar.classList.remove('hidden');
                quickCustomerTitle.textContent = `تشكيلة أصناف العميل المعتمدة: ${cust.name}`;

                // Update existing product dropdowns with grouped options
                const allSelects = itemsContainer.querySelectorAll('.item-product-select');
                allSelects.forEach(sel => {
                    const currentVal = sel.value;
                    sel.innerHTML = buildProductOptions(currentVal, custId);
                });
            } else {
                quickCustomerToolbar.classList.add('hidden');
            }
        }

        // Fast Fill for Customer Products (e.g. Abu Tarek 3 products)
        btnQuickFillCustomer.addEventListener('click', function () {
            const custId = customerSelect.value;
            if (!custId) return;

            const cust = customersData.find(c => c.id == custId);
            const custName = cust ? cust.name : '';

            // Get all products of this customer
            let prodsToLoad = productsData.filter(p => p.customer_id == custId);

            // If customer has fewer than 3, add standard 1*1 spoon or similar
            if (prodsToLoad.length === 1) {
                const p1 = productsData.find(p => p.code === 'PROD-CVR-1IN1');
                const p2 = productsData.find(p => p.code === 'PROD-CVR-2IN1');
                if (p2 && !prodsToLoad.some(p => p.id === p2.id)) prodsToLoad.push(p2);
                if (p1 && !prodsToLoad.some(p => p.id === p1.id)) prodsToLoad.push(p1);
            }

            if (prodsToLoad.length === 0) {
                // If no specific products, load standard couvert set
                const p4 = productsData.find(p => p.code === 'PROD-CVR-4IN1-ABU') || productsData[0];
                const p2 = productsData.find(p => p.code === 'PROD-CVR-2IN1');
                const p1 = productsData.find(p => p.code === 'PROD-CVR-1IN1');
                prodsToLoad = [p4, p2, p1].filter(Boolean);
            }

            // Clear current items and populate
            itemsContainer.innerHTML = '';
            itemCounter = 0;

            const defaultQuantities = [50000, 20000, 30000, 10000];

            prodsToLoad.forEach((p, idx) => {
                const qty = defaultQuantities[idx] || 25000;
                addItem(p.id, qty, p.selling_price, `توريد لصالح ${custName}`);
            });
        });

        // Event listener on Add Item button
        btnAddItem.addEventListener('click', function () {
            addItem('', 50000, null, '');
        });

        customerSelect.addEventListener('change', handleCustomerChange);

        // Initial setup
        const initialCustId = customerSelect.value;
        if (initialCustId) {
            handleCustomerChange();
            // If Abu Tarek is selected by default, populate his 3 products!
            const pAbu4 = productsData.find(p => p.code === 'PROD-CVR-4IN1-ABU');
            const pAbu2 = productsData.find(p => p.code === 'PROD-CVR-2IN1');
            const pAbu1 = productsData.find(p => p.code === 'PROD-CVR-1IN1');

            if (initialCustId == 1 && pAbu4 && pAbu2 && pAbu1) {
                addItem(pAbu4.id, 50000, pAbu4.selling_price, 'لوجو أزرق - كشري أبو طارق');
                addItem(pAbu2.id, 30000, pAbu2.selling_price, 'معلقة ومنديل 2*1');
                addItem(pAbu1.id, 20000, pAbu1.selling_price, 'معلقة مفردة 1*1 مغلفة');
            } else {
                addItem('', 50000, 2.15, '');
            }
        } else {
            // Add initial empty item row
            const firstProd = productsData[0];
            if (firstProd) {
                addItem(firstProd.id, 50000, firstProd.selling_price, '');
            } else {
                addItem('', 50000, 2.15, '');
            }
        }
    });
</script>
@endpush
