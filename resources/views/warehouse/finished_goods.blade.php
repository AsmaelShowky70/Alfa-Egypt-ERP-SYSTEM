@extends('layouts.app')

@section('title', 'مخزن المنتج التام (الكوڤير والأصناف الجاهزة للتسليم والبيع)')

@section('content')
<div class="space-y-6">

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
            <button type="button" onclick="openFinishedAddModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة منتج تام جديد</span>
            </button>
            <button type="button" onclick="openFinishedDispatchModal(null)" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
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

    <!-- Products Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $p)
            @php
                $isZero = $p->current_stock <= 0;
                $isLow = $p->current_stock <= $p->reorder_level && !$isZero;
                $factorySellingPrice = round($p->cost_price * 1.14, 2);
            @endphp
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4 relative {{ $isZero ? 'border-rose-300' : ($isLow ? 'border-amber-300' : '') }}">
                
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <!-- Product Real Photo with Zoom -->
                        <div class="relative group cursor-pointer flex-shrink-0" onclick="openFinishedZoomModal('{{ $p->image_url }}', '{{ addslashes($p->name) }}')">
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-16 h-16 object-cover rounded-2xl border border-slate-200 bg-white shadow-sm group-hover:scale-105 transition" loading="lazy">
                            <div class="absolute inset-0 bg-black/30 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[10px] text-slate-500 font-bold bg-slate-100 px-1.5 py-0.5 rounded">{{ $p->code }}</span>
                                @if($p->customer)
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded truncate">
                                        {{ $p->customer->name }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">
                                        عام (متاح للبيع)
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug truncate" title="{{ $p->name }}">{{ $p->name }}</h3>
                            @if($p->package_info)
                                <span class="text-[10px] text-blue-700 font-semibold flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-boxes-packing text-[9px]"></i>
                                    {{ $p->package_info }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Stock & Status Row -->
                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-bold">الرصيد بالمخزن</span>
                            <div class="text-base font-black {{ $isZero ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ number_format($p->current_stock) }}
                                <span class="text-xs font-bold text-slate-600">{{ $p->unit }}</span>
                            </div>
                        </div>

                        <div>
                            @if($isZero)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800">غير متوفر (0)</span>
                            @elseif($isLow)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">تحت حد الأمان</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">جاهز للتسليم</span>
                            @endif
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
                        <button type="button" 
                                onclick="openFinishedDispatchModal(this)"
                                data-id="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-unit="{{ $p->unit }}"
                                data-stock="{{ $p->current_stock }}"
                                data-price="{{ $p->selling_price }}"
                                data-customer="{{ $p->customer_id }}"
                                class="py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 shadow-sm">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>صرف وبيع</span>
                        </button>

                        <button type="button" 
                                onclick="openFinishedAdjustModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->unit }}', {{ (float)$p->current_stock }})"
                                class="py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>تسوية</span>
                        </button>

                        @if(Auth::user()->isOwner() || Auth::user()->isGM())
                            <button type="button" 
                                    onclick="openFinishedPricingModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ (float)$p->cost_price }}, {{ (float)$p->selling_price }})"
                                    class="py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                                <i class="fa-solid fa-coins"></i>
                                <span>تسعير</span>
                            </button>
                        @else
                            <button type="button" 
                                    onclick="openFinishedEditModal(this)"
                                    data-id="{{ $p->id }}"
                                    data-name="{{ $p->name }}"
                                    data-category="{{ $p->category }}"
                                    data-unit="{{ $p->unit }}"
                                    data-customer="{{ $p->customer_id }}"
                                    data-stock="{{ $p->current_stock }}"
                                    data-cost="{{ $p->cost_price }}"
                                    data-selling="{{ $p->selling_price }}"
                                    data-reorder="{{ $p->reorder_level }}"
                                    data-package="{{ $p->package_info }}"
                                    class="py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
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

    @if($products->hasPages())
        <div class="pt-4">
            {{ $products->links() }}
        </div>
    @endif

    <!-- MODAL 1: IMAGE ZOOM LIGHTBOX -->
    <div id="finishedZoomModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-xl w-full p-4 shadow-2xl space-y-3 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 id="finishedZoomTitle" class="font-black text-slate-900 text-sm"></h3>
                <button type="button" onclick="closeModal('finishedZoomModal')" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
            </div>
            <div class="bg-slate-50 p-2 rounded-2xl flex items-center justify-center">
                <img id="finishedZoomImg" src="" alt="" class="max-h-[70vh] rounded-xl object-contain shadow-sm">
            </div>
        </div>
    </div>

    <!-- MODAL 2: TOP MANAGEMENT COUVERT PRICING -->
    @if(Auth::user()->isOwner() || Auth::user()->isGM())
    <div id="finishedPricingModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-amber-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">تسعير الكوڤير والمنتج التام</h3>
                        <p class="text-[11px] text-amber-800 font-bold">خاص بـ د. عزت الفيل & م. بولا الفيل</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('finishedPricingModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.pricing.update') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="type" value="finished_good">
                <input type="hidden" id="pricing_prod_id" name="id" value="">

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1">
                    <div id="pricing_prod_name" class="font-black text-slate-900"></div>
                    <div class="text-slate-500">
                        تكلفة الخامات الحالية (BOM): 
                        <span id="pricing_prod_cost_label" class="font-bold text-slate-800"></span> ج.م
                    </div>
                    <div class="text-emerald-700 font-bold">
                        الحد الأدنى لسعر المصنع (+14% مصنعية): 
                        <span id="pricing_prod_factory_label" class="font-black"></span> ج.م
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">سعر التكلفة المحتسب للقطعة (ج.م) *</label>
                    <input type="number" step="0.01" min="0" id="pricing_prod_cost" name="cost_price" required oninput="calcFactoryPrice()" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">سعر البيع المعتمد للغير (ج.م) *</label>
                    <input type="number" step="0.01" min="0" id="pricing_prod_selling" name="selling_price" required class="w-full text-xs font-black text-emerald-800 rounded-xl border-amber-300 focus:ring-amber-500 focus:border-amber-500">
                    <p class="text-[10px] text-slate-400 mt-1">يجب ألا يقل سعر البيع عن تكلفة الخامات + 14% مصنعية تصنيع.</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('finishedPricingModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>اعتماد السعر الثابت</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL 3: ADD FINISHED GOOD -->
    <div id="finishedAddModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    <span>إضافة منتج تام / كوڤير جديد للمخزن</span>
                </h3>
                <button type="button" onclick="closeModal('finishedAddModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.finishedGoods.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم المنتج التام *</label>
                        <input type="text" name="name" required placeholder="مثال: كوڤير 4*1 كشري أبو طارق (معلقة، شوكة، وايبس، خلة)" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="كوڤير مطاعم متكامل">كوڤير مطاعم متكامل</option>
                            <option value="عبوات مناديل وايبس معطرة">عبوات مناديل وايبس معطرة</option>
                            <option value="علب جوانتي وبوليمرات">علب جوانتي وبوليمرات</option>
                            <option value="أكياس ومفارش سفرة">أكياس ومفارش سفرة</option>
                            <option value="أطقم شوك ومعالق مفردة">أطقم شوك ومعالق مفردة</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <select name="unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="قطعة">قطعة (كوڤير فردي)</option>
                            <option value="كرتونة">كرتونة</option>
                            <option value="باكت">باكت</option>
                            <option value="علبة">علبة</option>
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
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الافتتاحي بالمخزن *</label>
                        <input type="number" step="1" min="0" name="current_stock" value="0" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تكلفة الخامات للقطعة (BOM) ج.م *</label>
                        <input type="number" step="0.01" min="0" name="cost_price" value="1.25" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر البيع المعتمد ج.م *</label>
                        <input type="number" step="0.01" min="0" name="selling_price" value="1.65" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان (Reorder Level) *</label>
                        <input type="number" step="1" min="0" name="reorder_level" value="1000" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">مواصفات التعبئة (Package Info)</label>
                        <input type="text" name="package_info" placeholder="مثال: كرتونة تحتوي 1000 كوڤير / 10 باكت × 100 قطعة" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">صورة المنتج الواقعية</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('finishedAddModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
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

    <!-- MODAL 4: EDIT FINISHED GOOD -->
    <div id="finishedEditModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                    <span>تعديل المنتج التام: <span id="finishedEditTitle" class="text-emerald-700"></span></span>
                </h3>
                <button type="button" onclick="closeModal('finishedEditModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form id="finishedEditForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم المنتج *</label>
                        <input type="text" id="edit_fg_name" name="name" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <input type="text" id="edit_fg_category" name="category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <input type="text" id="edit_fg_unit" name="unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">العميل المخصص له</label>
                        <select id="edit_fg_customer" name="customer_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- متاح للبيع للغير (عام) --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد بالمخزن *</label>
                        <input type="number" step="1" min="0" id="edit_fg_stock" name="current_stock" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تكلفة القطعة (BOM) ج.م *</label>
                        <input type="number" step="0.01" min="0" id="edit_fg_cost" name="cost_price" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر البيع المعتمد ج.م *</label>
                        <input type="number" step="0.01" min="0" id="edit_fg_selling" name="selling_price" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان *</label>
                        <input type="number" step="1" min="0" id="edit_fg_reorder" name="reorder_level" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">مواصفات التعبئة (Package Info)</label>
                        <input type="text" id="edit_fg_package" name="package_info" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">تحديث صورة المنتج (اختياري)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('finishedEditModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
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

    <!-- MODAL 5: DIRECT SALE & DISPATCH FROM WAREHOUSE -->
    <div id="finishedDispatchModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                    <span>إذن صرف وبيع بضاعة من المخزن</span>
                </h3>
                <button type="button" onclick="closeModal('finishedDispatchModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.dispatchSale') }}" class="space-y-4 text-xs">
                @csrf
                <div class="bg-emerald-50/70 p-3 rounded-2xl border border-emerald-100 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-900">المنتج المختار للصرف:</span>
                        <span id="dispatch_product_name_label" class="font-black text-sm text-emerald-800"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>الرصيد المتاح حالياً بالمخزن:</span>
                        <span id="dispatch_current_stock_label" class="font-black text-slate-900"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اختر المنتج التام *</label>
                        <select id="dispatch_product_select" name="product_id" required onchange="onDispatchProductChange(this)" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" 
                                        data-name="{{ $prod->name }}" 
                                        data-stock="{{ $prod->current_stock }}" 
                                        data-unit="{{ $prod->unit }}" 
                                        data-price="{{ $prod->selling_price }}"
                                        data-customer="{{ $prod->customer_id }}">
                                    {{ $prod->name }} (متاح: {{ number_format($prod->current_stock) }} {{ $prod->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">العميل المستلم *</label>
                        <select id="dispatch_customer_id" name="customer_id" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- اختر العميل --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الكمية المصروفة للبيع *</label>
                        <input type="number" step="1" min="1" id="dispatch_quantity" name="quantity" value="1000" required oninput="calcDispatchTotal()" class="w-full text-xs font-black rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر بيع الوحدة (ج.م) *</label>
                        <input type="number" step="0.01" min="0" id="dispatch_unit_price" name="unit_price" required oninput="calcDispatchTotal()" class="w-full text-xs font-black rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">طريقة السداد والتحصيل *</label>
                        <select name="payment_type" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="cash">نقدي فوراً (إيداع الخزينة الرئيسية)</option>
                            <option value="credit">آجل على حساب العميل (فاتورة ومستحقات)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تاريخ الصرف والتسليم *</label>
                        <input type="date" name="dispatch_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم مندوب / سائق المستلم *</label>
                        <input type="text" name="received_by" required placeholder="مثال: أحمد عبد الله (سائق سيارة رقم 4215)" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">ملاحظات إذن الصرف</label>
                        <input type="text" name="notes" placeholder="رقم سيارة التحميل أو ملاحظات العميل..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-xs flex items-center justify-between">
                    <span class="font-bold text-slate-600">إجمالي قيمة الفاتورة المصروفة:</span>
                    <span id="dispatch_total_price_label" class="font-black text-emerald-700 text-base">0.00 ج.م</span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('finishedDispatchModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
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

    <!-- MODAL 6: FAST STOCK ADJUSTMENT FOR FINISHED GOODS -->
    <div id="finishedAdjustModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-blue-600"></i>
                    <span>تسوية رصيد جردي للمنتج التام</span>
                </h3>
                <button type="button" onclick="closeModal('finishedAdjustModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.adjustStock') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="item_type" value="product">
                <input type="hidden" id="adjust_fg_id" name="item_id" value="">

                <div class="bg-blue-50/60 p-3 rounded-2xl border border-blue-100 space-y-1">
                    <div id="adjust_fg_name" class="font-bold text-blue-900 text-sm"></div>
                    <div class="text-blue-700">
                        الرصيد الدفتري الحالي: 
                        <span id="adjust_fg_stock" class="font-black text-sm"></span> 
                        <span id="adjust_fg_unit"></span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الجديد بعد الجرد *</label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="1" min="0" id="adjust_fg_new_stock" name="new_stock" required class="flex-1 text-sm font-black rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <span id="adjust_fg_unit_label" class="font-bold text-slate-600"></span>
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
                    <button type="button" onclick="closeModal('finishedAdjustModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
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

<!-- Vanilla JS Modal Controller (100% Fail-Safe, Independent of CDN) -->
<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }

    function openFinishedAddModal() {
        openModal('finishedAddModal');
    }

    function openFinishedEditModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const category = btn.dataset.category;
        const unit = btn.dataset.unit;
        const customer = btn.dataset.customer || '';
        const stock = btn.dataset.stock;
        const cost = btn.dataset.cost;
        const selling = btn.dataset.selling;
        const reorder = btn.dataset.reorder;
        const packageInfo = btn.dataset.package || '';

        document.getElementById('finishedEditTitle').textContent = name;
        document.getElementById('finishedEditForm').action = "{{ url('warehouse/finished-goods') }}/" + id;
        document.getElementById('edit_fg_name').value = name;
        document.getElementById('edit_fg_category').value = category;
        document.getElementById('edit_fg_unit').value = unit;
        document.getElementById('edit_fg_customer').value = customer;
        document.getElementById('edit_fg_stock').value = stock;
        document.getElementById('edit_fg_cost').value = cost;
        document.getElementById('edit_fg_selling').value = selling;
        document.getElementById('edit_fg_reorder').value = reorder;
        document.getElementById('edit_fg_package').value = packageInfo;

        openModal('finishedEditModal');
    }

    function openFinishedAdjustModal(id, name, unit, stock) {
        document.getElementById('adjust_fg_id').value = id;
        document.getElementById('adjust_fg_name').textContent = name;
        document.getElementById('adjust_fg_stock').textContent = stock;
        document.getElementById('adjust_fg_unit').textContent = unit;
        document.getElementById('adjust_fg_unit_label').textContent = unit;
        document.getElementById('adjust_fg_new_stock').value = stock;

        openModal('finishedAdjustModal');
    }

    function openFinishedPricingModal(id, name, cost, selling) {
        const modal = document.getElementById('finishedPricingModal');
        if (!modal) return;

        document.getElementById('pricing_prod_id').value = id;
        document.getElementById('pricing_prod_name').textContent = name;
        document.getElementById('pricing_prod_cost').value = cost;
        document.getElementById('pricing_prod_selling').value = selling;
        document.getElementById('pricing_prod_cost_label').textContent = Number(cost).toFixed(2);
        document.getElementById('pricing_prod_factory_label').textContent = (Number(cost) * 1.14).toFixed(2);

        openModal('finishedPricingModal');
    }

    function calcFactoryPrice() {
        const cost = parseFloat(document.getElementById('pricing_prod_cost').value) || 0;
        document.getElementById('pricing_prod_cost_label').textContent = cost.toFixed(2);
        document.getElementById('pricing_prod_factory_label').textContent = (cost * 1.14).toFixed(2);
    }

    function openFinishedDispatchModal(btn) {
        const select = document.getElementById('dispatch_product_select');
        if (btn) {
            const id = btn.dataset.id;
            select.value = id;
        }
        onDispatchProductChange(select);
        openModal('finishedDispatchModal');
    }

    function onDispatchProductChange(selectEl) {
        const selected = selectEl.options[selectEl.selectedIndex];
        if (!selected) return;

        const name = selected.dataset.name || selected.text;
        const stock = selected.dataset.stock || 0;
        const unit = selected.dataset.unit || 'قطعة';
        const price = selected.dataset.price || 0;
        const customer = selected.dataset.customer || '';

        document.getElementById('dispatch_product_name_label').textContent = name;
        document.getElementById('dispatch_current_stock_label').textContent = Number(stock).toLocaleString() + ' ' + unit;
        document.getElementById('dispatch_unit_price').value = price;
        if (customer) {
            document.getElementById('dispatch_customer_id').value = customer;
        }
        calcDispatchTotal();
    }

    function calcDispatchTotal() {
        const qty = parseFloat(document.getElementById('dispatch_quantity').value) || 0;
        const price = parseFloat(document.getElementById('dispatch_unit_price').value) || 0;
        const total = qty * price;
        document.getElementById('dispatch_total_price_label').textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج.م';
    }

    function openFinishedZoomModal(src, title) {
        document.getElementById('finishedZoomImg').src = src;
        document.getElementById('finishedZoomTitle').textContent = title;
        openModal('finishedZoomModal');
    }

    // Backdrop click listener - clicking outside modal dialog closes it immediately
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.modal-wrapper').forEach(function(modal) {
            if (e.target === modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        });
    });

    // Escape key listener closes all modals
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-wrapper').forEach(function(modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            });
        }
    });
</script>
@endsection
