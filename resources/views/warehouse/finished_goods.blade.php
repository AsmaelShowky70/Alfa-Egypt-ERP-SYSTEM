@extends('layouts.app')

@section('title', 'مخزن المنتج التام (الكوڤير والأصناف الجاهزة للتسليم والبيع)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showAdjustModal: false,
    showDispatchModal: false,
    showPricingModal: false,
    showZoomModal: false,
    zoomImage: '',
    zoomTitle: '',
    editItem: { id: '', name: '', category: '', unit: '', customer_id: '', cost_price: 0, selling_price: 0, current_stock: 0, reorder_level: 0, package_info: '', description: '' },
    adjustItem: { id: '', name: '', unit: '', current_stock: 0, new_stock: 0, reason: '', notes: '' },
    dispatchItem: { id: '', name: '', unit: '', current_stock: 0, selling_price: 0, customer_id: '', quantity: 1, total: 0, payment_type: 'credit', received_by: '', dispatch_date: '{{ date('Y-m-d') }}', notes: '' },
    pricingItem: { id: '', name: '', cost_price: 0, selling_price: 0, factory_price: 0 },
    openEdit(p) {
        this.editItem = { ...p };
        this.showEditModal = true;
    },
    openAdjust(p) {
        this.adjustItem = {
            id: p.id,
            name: p.name,
            unit: p.unit,
            current_stock: p.current_stock,
            new_stock: p.current_stock,
            reason: 'جرد فعلي لمخزن المنتج التام',
            notes: ''
        };
        this.showAdjustModal = true;
    },
    openDispatch(p) {
        this.dispatchItem = {
            id: p.id,
            name: p.name,
            unit: p.unit,
            current_stock: p.current_stock,
            selling_price: p.selling_price,
            customer_id: p.customer_id || '',
            quantity: 1000,
            unit_price: p.selling_price,
            payment_type: 'credit',
            received_by: '',
            dispatch_date: '{{ date('Y-m-d') }}',
            notes: ''
        };
        this.showDispatchModal = true;
    },
    openPricing(p) {
        this.pricingItem = {
            id: p.id,
            name: p.name,
            cost_price: p.cost_price,
            selling_price: p.selling_price,
            factory_price: (Number(p.cost_price) * 1.14).toFixed(2)
        };
        this.showPricingModal = true;
    },
    openZoom(img, title) {
        this.zoomImage = img;
        this.zoomTitle = title;
        this.showZoomModal = true;
    }
}">

    <!-- Top Management Couvert Pricing & 14% Factory Markup Banner -->
    <div class="bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-emerald-500/40">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-2xl shadow-inner">
                <i class="fa-solid fa-calculator text-emerald-300"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-400 text-slate-900 shadow-sm">
                        معادلة التسعير المعتمدة
                    </span>
                    <span class="text-xs text-emerald-100 font-bold">إشراف: د. عزت الفيل & م. بولا الفيل</span>
                </div>
                <h2 class="text-lg font-black mt-0.5">تسعير الكوڤير الثابت + 14% مصنعية تصنيع داخلي للبيع للغير</h2>
                <p class="text-xs text-emerald-100/90 mt-0.5">
                    سعر الكوڤير ثابت تحدده الإدارة العليا، وللمصانع الداخلية: <span class="font-bold underline text-white">تكلفة الخامات (BOM) + 14% مصنعية = سعر البيع للغير</span>.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-white/10 text-xs font-black text-emerald-200 border border-emerald-400/30">
                <i class="fa-solid fa-percent ml-1"></i> مصنعية 14% مبرمجة آلياً
            </span>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-emerald-600"></i>
                <span>مخزن المنتج التام (الكوڤير، عبوات الوايبس، علب الجوانتي)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">متابعة دقيقة لأرصدة الكوڤير الجاهزة للتسليم للعملاء أو البيع المباشر للغير مع الترحيل المالي الآلي</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showAddModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة منتج تام جديد</span>
            </button>
            <button @click="openDispatch(products[0] || {})" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>صرف وبيع من المخزن</span>
            </button>
            <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'finished_goods']) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>جرد المنتج التام</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 px-2 py-1">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('warehouse.finishedGoods') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث باسم أو كود المنتج أو الطرد</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="مثال: أبو طارق، رويان، إتوال، كرتونة..." class="w-full text-xs rounded-xl border-slate-200 pr-8 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-search absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">العميل المخصص له</label>
                <select name="customer_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- كافة العملاء والبيع العام --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">حالة الرصيد التام</label>
                <select name="filter" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- كافة الأرصدة --</option>
                    <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>تحت حد الأمان (عجز تسليم)</option>
                    <option value="zero_stock" {{ request('filter') == 'zero_stock' ? 'selected' : '' }}>رصيد صفر (غير متوفر)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية المنتجات</span>
                </button>
                <a href="{{ route('warehouse.finishedGoods') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $p)
            @php
                $isZero = $p->current_stock <= 0;
                $isLow = $p->current_stock <= $p->reorder_level && !$isZero;
                $factorySellingPrice = round($p->cost_price * 1.14, 2);
            @endphp
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition {{ $isZero ? 'border-rose-300 bg-rose-50/10' : '' }}">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="font-mono text-xs font-bold text-slate-400">{{ $p->code }}</span>
                            <h3 class="font-black text-base text-slate-900">{{ $p->name }}</h3>
                            @if($p->customer)
                                <p class="text-xs font-bold text-brand-700 flex items-center gap-1">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>مخصص لعميل: {{ $p->customer->name }}</span>
                                </p>
                            @else
                                <p class="text-xs font-bold text-slate-500 flex items-center gap-1">
                                    <i class="fa-solid fa-store"></i>
                                    <span>متاح للبيع للغير من المخزن</span>
                                </p>
                            @endif
                        </div>
                        <div class="relative group cursor-pointer" @click="openZoom('{{ $p->image_url }}', '{{ $p->name }}')">
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-16 h-16 rounded-2xl object-cover bg-white p-1 border border-slate-200 shadow-sm group-hover:scale-105 transition" loading="lazy">
                            <div class="absolute inset-0 bg-black/30 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </div>
                        </div>
                    </div>

                    @if($p->package_info)
                        <div class="text-xs text-blue-800 bg-blue-50/80 p-2.5 rounded-xl border border-blue-100 flex items-center gap-1.5 font-bold">
                            <i class="fa-solid fa-boxes-packing text-blue-600"></i>
                            <span>{{ $p->package_info }}</span>
                        </div>
                    @endif

                    @if($p->description)
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $p->description }}</p>
                    @endif

                    <!-- Stock & Cost Card -->
                    <div class="grid grid-cols-2 gap-2 text-center text-xs bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">الرصيد التام المتاح</span>
                            <span class="font-black text-base {{ $isZero ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($p->current_stock) }} {{ $p->unit }}
                            </span>
                            @if($isZero)
                                <span class="inline-block text-[9px] font-bold bg-rose-100 text-rose-700 px-1 rounded">رصيد صفري</span>
                            @elseif($isLow)
                                <span class="inline-block text-[9px] font-bold bg-amber-100 text-amber-700 px-1 rounded">تحت حد الأمان</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">إجمالي القيمة المخزنية</span>
                            <span class="font-black text-emerald-800 text-base">
                                {{ number_format($p->current_stock * $p->cost_price, 2) }} ج.م
                            </span>
                        </div>
                    </div>

                    <!-- 14% Internal Factory Markup & Couvert Pricing Calculation -->
                    <div class="p-2.5 rounded-xl bg-slate-100/80 border border-slate-200 text-xs space-y-1">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>تكلفة الخامات (BOM):</span>
                            <span class="font-black text-slate-800">{{ number_format($p->cost_price, 2) }} ج.م</span>
                        </div>
                        <div class="flex items-center justify-between text-emerald-700 font-bold">
                            <span>سعر البيع للغير (+14% مصنعية):</span>
                            <span class="font-black text-sm text-emerald-800">{{ number_format($factorySellingPrice, 2) }} ج.م</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 text-[10px]">
                            <span>السعر الثابت المعتمد للإدارة:</span>
                            <span class="font-black text-brand-700 text-xs">{{ number_format($p->selling_price, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 pt-3 border-t border-slate-100">
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-1.5 pt-1">
                        <button type="button" @click="openDispatch({{ json_encode($p) }})" class="py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 shadow-sm">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>صرف وبيع</span>
                        </button>
                        <button type="button" @click="openAdjust({{ json_encode($p) }})" class="py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>تسوية</span>
                        </button>
                        @if(Auth::user()->isOwner() || Auth::user()->isGM())
                            <button type="button" @click="openPricing({{ json_encode($p) }})" class="py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                                <i class="fa-solid fa-coins"></i>
                                <span>تسعير</span>
                            </button>
                        @else
                            <button type="button" @click="openEdit({{ json_encode($p) }})" class="py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>تعديل</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center bg-white rounded-3xl border border-dashed border-slate-200 text-slate-400 space-y-2">
                <i class="fa-solid fa-box-open text-4xl text-slate-300"></i>
                <p class="text-sm font-bold text-slate-600">لا توجد منتجات تامة مسجلة مطابقة لشرط البحث.</p>
            </div>
        @endforelse
    </div>

    <div class="pt-4 border-t border-slate-100">
        {{ $products->links() }}
    </div>

    <!-- MODAL: IMAGE ZOOM LIGHTBOX -->
    <div x-show="showZoomModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="showZoomModal = false" class="bg-white rounded-3xl max-w-xl w-full p-4 shadow-2xl space-y-3 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="font-black text-slate-900 text-sm" x-text="zoomTitle"></h3>
                <button @click="showZoomModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
            </div>
            <div class="bg-slate-50 p-2 rounded-2xl flex items-center justify-center">
                <img :src="zoomImage" :alt="zoomTitle" class="max-h-[70vh] rounded-xl object-contain shadow-sm">
            </div>
            <div class="text-center text-xs text-slate-500 font-medium">صورة المنتج التام الفعلية من جرد المخزن المركزي</div>
        </div>
    </div>

    <!-- MODAL: TOP MANAGEMENT COUVERT PRICING -->
    @if(Auth::user()->isOwner() || Auth::user()->isGM())
    <div x-show="showPricingModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showPricingModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-amber-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">تسعير الكوڤير ومصنعية الـ 14%</h3>
                        <p class="text-[11px] text-amber-800 font-bold">خاص بـ د. عزت الفيل & م. بولا الفيل</p>
                    </div>
                </div>
                <button @click="showPricingModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.pricing.update') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="product_id" :value="pricingItem.id">

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1">
                    <div class="font-black text-slate-900" x-text="pricingItem.name"></div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">تكلفة الخامات المباشرة (BOM) ج.م *</label>
                    <input type="number" step="0.01" min="0" name="cost_price" x-model="pricingItem.cost_price" @input="pricingItem.factory_price = (Number(pricingItem.cost_price) * 1.14).toFixed(2)" required class="w-full text-xs font-black rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <div class="flex items-center justify-between font-bold text-emerald-900">
                        <span>سعر البيع للغير بعد إضافة 14% مصنعية:</span>
                        <span class="font-black text-base text-emerald-700" x-text="pricingItem.factory_price + ' ج.م'"></span>
                    </div>
                    <p class="text-[10px] text-emerald-700 mt-1">يحتسب آلياً: التكلفة × 1.14 طبقاً لقواعد التصنيع الداخلي.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">سعر بيع الكوڤير الثابت المعتمد (ج.م) *</label>
                    <input type="number" step="0.01" min="0" name="selling_price" x-model="pricingItem.selling_price" required class="w-full text-xs font-black rounded-xl border-amber-300 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showPricingModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>اعتماد التسعير والمصنعية</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL: ADD FINISHED GOOD -->
    <div x-show="showAddModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    <span>إضافة منتج تام جديد لمخزن الكوڤير</span>
                </h3>
                <button @click="showAddModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.finishedGoods.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم المنتج التام *</label>
                        <input type="text" name="name" required placeholder="مثال: كوڤير 4 في 1 أزرق - كشري أبو طارق، علبة جوانتي طعام..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="كوڤير مخصص للعملاء">كوڤير مخصص للعملاء</option>
                            <option value="كوڤير تجاري عام">كوڤير تجاري عام (بيع للغير)</option>
                            <option value="مستلزمات نظافة وضيافة">مستلزمات نظافة وضيافة (جوانتي، وايبس)</option>
                            <option value="مفارش وسفر">مفارش وسفر</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">وحدة القياس *</label>
                        <select name="unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="قطعة">قطعة (كوڤير)</option>
                            <option value="علبة">علبة</option>
                            <option value="كرتونة">كرتونة</option>
                            <option value="باكت">باكت</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">العميل المخصص له (اختياري)</label>
                        <select name="customer_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- متاح للبيع للغير (عام) --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الحالي بالمخزن *</label>
                        <input type="number" step="1" min="0" name="current_stock" value="0" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تكلفة إنتاج القطعة (BOM) ج.م *</label>
                        <input type="number" step="0.01" min="0" name="cost_price" value="1.00" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر البيع المعتمد للقطعة ج.م *</label>
                        <input type="number" step="0.01" min="0" name="selling_price" value="1.50" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان وإعادة الطلب *</label>
                        <input type="number" step="1" min="0" name="reorder_level" value="5000" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">مواصفات الكرتونة / التعبئة (Package Info)</label>
                        <input type="text" name="package_info" placeholder="مثال: كرتونة تحتوي 1000 قطعة" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">صورة المنتج التام الفعلية</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">وصف المنتج ومكوناته التفصيلية</label>
                        <textarea name="description" rows="2" placeholder="اكتب مكونات الكوڤير (معلقة + شوكة + منديل + خلة...)" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-save"></i>
                        <span>حفظ وإضافة للمخزن</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT FINISHED GOOD -->
    <div x-show="showEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                    <span>تعديل المنتج التام: <span class="text-emerald-700" x-text="editItem.name"></span></span>
                </h3>
                <button @click="showEditModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form :action="'{{ url('warehouse/finished-goods') }}/' + editItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم المنتج *</label>
                        <input type="text" name="name" x-model="editItem.name" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <input type="text" name="category" x-model="editItem.category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <input type="text" name="unit" x-model="editItem.unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">العميل المخصص له</label>
                        <select name="customer_id" x-model="editItem.customer_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- متاح للبيع للغير (عام) --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد بالمخزن *</label>
                        <input type="number" step="1" min="0" name="current_stock" x-model="editItem.current_stock" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تكلفة القطعة (BOM) ج.م *</label>
                        <input type="number" step="0.01" min="0" name="cost_price" x-model="editItem.cost_price" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر البيع المعتمد ج.م *</label>
                        <input type="number" step="0.01" min="0" name="selling_price" x-model="editItem.selling_price" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان *</label>
                        <input type="number" step="1" min="0" name="reorder_level" x-model="editItem.reorder_level" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">مواصفات التعبئة (Package Info)</label>
                        <input type="text" name="package_info" x-model="editItem.package_info" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">تحديث صورة المنتج (اختياري)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showEditModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: DIRECT SALE & DISPATCH FROM WAREHOUSE -->
    <div x-show="showDispatchModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showDispatchModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                    <span>إذن صرف وبيع بضاعة من المخزن</span>
                </h3>
                <button @click="showDispatchModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.dispatchSale') }}" class="space-y-4">
                @csrf
                <div class="bg-emerald-50/70 p-3 rounded-2xl border border-emerald-100 space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-900">المنتج المختار للصرف:</span>
                        <span class="font-black text-sm text-emerald-800" x-text="dispatchItem.name"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>الرصيد المتاح حالياً بالمخزن:</span>
                        <span class="font-black text-slate-900" x-text="dispatchItem.current_stock + ' ' + dispatchItem.unit"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اختر المنتج التام *</label>
                        <select name="product_id" x-model="dispatchItem.id" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-stock="{{ $p->current_stock }}" data-price="{{ $p->selling_price }}">{{ $p->name }} (متاح: {{ number_format($p->current_stock) }} {{ $p->unit }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">العميل المستلم *</label>
                        <select name="customer_id" x-model="dispatchItem.customer_id" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- اختر العميل --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الكمية المصروفة للبيع *</label>
                        <input type="number" step="1" min="1" name="quantity" x-model="dispatchItem.quantity" required class="w-full text-xs font-black rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر بيع الوحدة (ج.م) *</label>
                        <input type="number" step="0.01" min="0" name="unit_price" x-model="dispatchItem.selling_price" required class="w-full text-xs font-black rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">طريقة السداد والتحصيل *</label>
                        <select name="payment_type" x-model="dispatchItem.payment_type" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="cash">نقدي فوراً (إيداع الخزينة الرئيسية)</option>
                            <option value="credit">آجل على حساب العميل (فاتورة ومستحقات)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تاريخ الصرف والتسليم *</label>
                        <input type="date" name="dispatch_date" x-model="dispatchItem.dispatch_date" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم مندوب / سائق المستلم *</label>
                        <input type="text" name="received_by" x-model="dispatchItem.received_by" required placeholder="مثال: أحمد عبد الله (سائق سيارة رقم 4215)" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">ملاحظات إذن الصرف</label>
                        <input type="text" name="notes" placeholder="رقم سيارة التحميل أو ملاحظات العميل..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-xs flex items-center justify-between">
                    <span class="font-bold text-slate-600">إجمالي قيمة الفاتورة المصروفة:</span>
                    <span class="font-black text-emerald-700 text-base" x-text="Number(dispatchItem.quantity * dispatchItem.selling_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج.م'"></span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showDispatchModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-receipt"></i>
                        <span>إصدار إذن الصرف وترحيل الحسابات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: FAST STOCK ADJUSTMENT FOR FINISHED GOODS -->
    <div x-show="showAdjustModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAdjustModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-blue-600"></i>
                    <span>تسوية رصيد جردي للمنتج التام</span>
                </h3>
                <button @click="showAdjustModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.adjustStock') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="item_type" value="product">
                <input type="hidden" name="item_id" :value="adjustItem.id">

                <div class="bg-blue-50/60 p-3 rounded-2xl border border-blue-100 space-y-1 text-xs">
                    <div class="font-bold text-blue-900 text-sm" x-text="adjustItem.name"></div>
                    <div class="text-blue-700">
                        الرصيد الدفتري الحالي: 
                        <span class="font-black text-sm" x-text="adjustItem.current_stock"></span> 
                        <span x-text="adjustItem.unit"></span>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الجديد بعد الجرد *</label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="1" min="0" name="new_stock" x-model="adjustItem.new_stock" required class="flex-1 text-sm font-black rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <span class="font-bold text-slate-600" x-text="adjustItem.unit"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سبب التسوية الجردية *</label>
                        <select name="reason" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="جرد دوري لمخزن المنتج التام ومطابقة الكراتين">جرد دوري لمخزن المنتج التام ومطابقة الكراتين</option>
                            <option value="إضافة إنتاج وردية سابق لم يقيد">إضافة إنتاج وردية سابق لم يقيد</option>
                            <option value="تسوية تلفيات شحن أو تخزين">تسوية تلفيات شحن أو تخزين</option>
                            <option value="أخرى">أخرى (مذكورة بالملاحظات)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ملاحظات إضافية</label>
                        <textarea name="notes" rows="2" placeholder="اكتب تفاصيل محضر الجرد..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAdjustModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check-double"></i>
                        <span>اعتماد تسوية الرصيد</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
