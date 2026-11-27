@extends('layouts.app')

@section('title', 'المواد الخام ومستلزمات الكوڤير (المخزن المركزي)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showAdjustModal: false,
    showPricingModal: false,
    showZoomModal: false,
    zoomImage: '',
    zoomTitle: '',
    editItem: { id: '', name: '', category: '', unit: '', current_stock: 0, reorder_level: 0, unit_cost: 0, package_info: '', warehouse_location: '', supplier_id: '', source_factory_id: '' },
    adjustItem: { id: '', name: '', unit: '', current_stock: 0, new_stock: 0, reason: '', notes: '' },
    pricingItem: { id: '', name: '', unit: '', unit_cost: 0 },
    openEdit(item) {
        this.editItem = { ...item };
        this.showEditModal = true;
    },
    openAdjust(item) {
        this.adjustItem = {
            id: item.id,
            name: item.name,
            unit: item.unit,
            current_stock: item.current_stock,
            new_stock: item.current_stock,
            reason: 'جرد فعلي للمخزن',
            notes: ''
        };
        this.showAdjustModal = true;
    },
    openPricing(item) {
        this.pricingItem = {
            id: item.id,
            name: item.name,
            unit: item.unit,
            unit_cost: item.unit_cost
        };
        this.showPricingModal = true;
    },
    openZoom(img, title) {
        this.zoomImage = img;
        this.zoomTitle = title;
        this.showZoomModal = true;
    }
}">

    <!-- Top Management Pricing Authority Banner (دكتور عزت وم. بولا) -->
    @if(Auth::user()->isOwner() || Auth::user()->isGM())
        <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-amber-800 text-white rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-amber-500/50">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-2xl shadow-inner">
                    <i class="fa-solid fa-crown text-amber-200"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-white text-amber-900 shadow-sm">
                            صلاحية الإدارة العليا الحصرية
                        </span>
                        <span class="text-xs text-amber-100 font-bold">د. عزت الفيل (المالك) & م. بولا الفيل (المدير العام)</span>
                    </div>
                    <h2 class="text-lg font-black mt-0.5">لوحة اعتماد أسعار تكلفة الخامات وحسابات الربحية</h2>
                    <p class="text-xs text-amber-100/90 mt-0.5">تحديد التكلفة الفعلية للخامة محصور بالإدارة العليا لمنع التلاعب وتحديد هوامش أرباح الكوڤير بدقة</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-black/20 text-xs font-bold text-amber-100 border border-white/10">
                    <i class="fa-solid fa-lock-open ml-1"></i> وضع التسعير مفعل
                </span>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cubes-stacked text-amber-600"></i>
                <span>المواد الخام ومستلزمات الإنتاج (المخزن المركزي)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">عرض دقيق لرصيد المخزن الفعلي بالصور الحية، أوزان الرولات بالكيلو، مستلزمات الحقن، والورقيات بالبالة</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showAddModal = true" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة خامة جديدة</span>
            </button>
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-receipt"></i>
                <span>إذن استلام خامات</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>بدء جرد يومي</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 px-2 py-1">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('warehouse.rawMaterials') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث باسم أو كود الخامة أو الطرد</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث مثلاً: سيلوفان، معلقة، 116، كرتونة..." class="w-full text-xs rounded-xl border-slate-200 pr-8 focus:ring-amber-500 focus:border-amber-500">
                    <i class="fa-solid fa-search absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">تصنيف الخامة</label>
                <select name="category" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">-- كافة التصنيفات --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">حالة المخزون</label>
                <select name="filter" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">-- كافة الأرصدة --</option>
                    <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>تحت حد الأمان (عجز)</option>
                    <option value="zero_stock" {{ request('filter') == 'zero_stock' ? 'selected' : '' }}>رصيد صفري تماماً</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية الأصناف</span>
                </button>
                <a href="{{ route('warehouse.rawMaterials') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Materials Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                عرض {{ $materials->count() }} من أصل {{ $materials->total() }} خامة ومستلزم بالمخزن
            </span>
            <div class="text-xs text-slate-400">
                إجمالي قيمة المخزون الحالي في الصفحة: 
                <span class="font-bold text-slate-900">{{ number_format($materials->sum(fn($m) => $m->current_stock * $m->unit_cost), 2) }} ج.م</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-3">صورة ورصيد الصنف</th>
                        <th class="py-3 px-3">كود الخامة</th>
                        <th class="py-3 px-3">اسم الخامة والمواصفات التفصيلية</th>
                        <th class="py-3 px-3">التصنيف</th>
                        <th class="py-3 px-3">الوحدة</th>
                        <th class="py-3 px-3">تكلفة الوحدة (اعتماد الإدارة)</th>
                        <th class="py-3 px-3">إجمالي القيمة</th>
                        <th class="py-3 px-3">حد الأمان</th>
                        <th class="py-3 px-3">الموقع</th>
                        <th class="py-3 px-3 text-center">إجراءات المخزن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($materials as $m)
                        @php
                            $isZero = $m->current_stock <= 0;
                            $isLow = $m->current_stock <= $m->reorder_level && !$isZero;
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isZero ? 'bg-rose-50/20' : ($isLow ? 'bg-amber-50/20' : '') }}">
                            <!-- صورة ورصيد الصنف بالخانة المخصصة -->
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative group cursor-pointer" @click="openZoom('{{ $m->image_url }}', '{{ $m->name }}')">
                                        <img src="{{ $m->image_url }}" alt="{{ $m->name }}" class="w-14 h-14 object-cover rounded-2xl border border-slate-200 bg-white shadow-sm group-hover:scale-105 transition" loading="lazy">
                                        <div class="absolute inset-0 bg-black/30 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-black text-sm {{ $isZero ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900') }}">
                                            {{ number_format($m->current_stock, 2) }}
                                            <span class="text-xs font-bold text-slate-600">{{ $m->unit }}</span>
                                        </div>
                                        @if($isZero)
                                            <span class="inline-block text-[9px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded mt-0.5">رصيد صفري</span>
                                        @elseif($isLow)
                                            <span class="inline-block text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded mt-0.5">تحت حد الأمان</span>
                                        @else
                                            <span class="inline-block text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded mt-0.5">متوفر وجاهز</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-3 font-mono font-bold text-slate-700">{{ $m->code }}</td>

                            <td class="py-3 px-3">
                                <div class="font-black text-slate-900 text-sm">{{ $m->name }}</div>
                                @if($m->package_info)
                                    <div class="text-[11px] text-blue-700 font-bold mt-1 flex items-center gap-1.5 bg-blue-50/70 px-2 py-0.5 rounded-lg border border-blue-100 w-fit">
                                        <i class="fa-solid fa-boxes-packing text-blue-500"></i>
                                        <span>{{ $m->package_info }}</span>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 px-3">
                                <span class="inline-block px-2.5 py-1 rounded-xl text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $m->category }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-slate-600 font-bold">{{ $m->unit }}</td>

                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-brand-700 text-sm">
                                        {{ number_format($m->unit_cost, 2) }} ج.م
                                    </span>
                                    @if(Auth::user()->isOwner() || Auth::user()->isGM())
                                        <button type="button" @click="openPricing({{ json_encode($m) }})" title="تسعير الإدارة العليا" class="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </button>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 block">
                                    @if(Auth::user()->isOwner() || Auth::user()->isGM())
                                        معتمد للإدارة العليا
                                    @else
                                        تسعير محمي ومقيد
                                    @endif
                                </span>
                            </td>

                            <td class="py-3 px-3 font-black text-slate-800">
                                {{ number_format($m->current_stock * $m->unit_cost, 2) }} ج.م
                            </td>

                            <td class="py-3 px-3 text-slate-500 font-mono">{{ number_format($m->reorder_level) }} {{ $m->unit }}</td>

                            <td class="py-3 px-3 text-slate-500 font-medium">
                                <i class="fa-solid fa-location-dot text-slate-400 ml-1"></i>
                                {{ $m->warehouse_location ?? 'عنبر عام' }}
                            </td>

                            <td class="py-3 px-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if(Auth::user()->isOwner() || Auth::user()->isGM())
                                        <button type="button" @click="openPricing({{ json_encode($m) }})" title="تسعير الإدارة العليا" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-coins"></i>
                                            <span>تسعير</span>
                                        </button>
                                    @endif
                                    <button type="button" @click="openAdjust({{ json_encode($m) }})" title="تسوية رصيد جردي" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-scale-balanced"></i>
                                        <span>تسوية</span>
                                    </button>
                                    <button type="button" @click="openEdit({{ json_encode($m) }})" title="تعديل بيانات الخامة" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">
                                لا توجد خامات مطابقة للبحث أو التصفية الحالية.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $materials->links() }}
        </div>
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
            <div class="text-center text-xs text-slate-500 font-medium">صورة الصنف الفعلية الملتقطة من جرد المخزن المركزي</div>
        </div>
    </div>

    <!-- MODAL: TOP MANAGEMENT PRICING (دكتور عزت وم. بولا) -->
    @if(Auth::user()->isOwner() || Auth::user()->isGM())
    <div x-show="showPricingModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showPricingModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-amber-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900">اعتماد سعر تكلفة الخامة</h3>
                        <p class="text-[11px] text-amber-800 font-bold">خاص بـ د. عزت الفيل & م. بولا الفيل</p>
                    </div>
                </div>
                <button @click="showPricingModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.pricing.update') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="material_id" :value="pricingItem.id">

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1">
                    <div class="font-black text-slate-900" x-text="pricingItem.name"></div>
                    <div class="text-slate-500">الوحدة: <span class="font-bold text-slate-800" x-text="pricingItem.unit"></span></div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">سعر التكلفة الجديد المعتمد (ج.م) *</label>
                    <div class="relative">
                        <input type="number" step="0.001" min="0" name="unit_cost" x-model="pricingItem.unit_cost" required class="w-full text-base font-black rounded-xl border-amber-300 focus:ring-amber-500 focus:border-amber-500 pr-3 pl-12">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">ج.م</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">سيتم استخدام هذا السعر لحساب ربحية الكوڤير وتسعير أوامر الشغل تلقائياً.</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showPricingModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>اعتماد السعر وتحديث الربحية</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL: ADD RAW MATERIAL -->
    <div x-show="showAddModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-amber-600"></i>
                    <span>إضافة خامة / مستلزم جديد للمخزن</span>
                </h3>
                <button @click="showAddModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.rawMaterials.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم الخامة / المستلزم *</label>
                        <input type="text" name="name" required placeholder="مثال: رول سيلوفان مطبوع أبو طارق، معلقة فيرساتشي أبيض..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                            <option value="رولات سيلوفان وبوليمرات">رولات سيلوفان وبوليمرات</option>
                            <option value="مستلزمات حقن وبلاستيك">مستلزمات حقن وبلاستيك (معالق، شوك، سكاكين، شاليموه)</option>
                            <option value="مناديل وورقيات">مناديل وورقيات (بالات، مناديل معطرة)</option>
                            <option value="خامات بهارات ومواد غذائية">خامات بهارات ومواد غذائية (ملح، فلفل، كاتشب، معطر)</option>
                            <option value="كراتين ومواد تغليف">كراتين ومواد تغليف</option>
                            <option value="مستلزمات نظافة وضيافة">مستلزمات نظافة وضيافة (جوانتيات، صابون، خلة)</option>
                            <option value="قطع غيار ماكينات">قطع غيار ماكينات</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">وحدة القياس والصرف *</label>
                        <select name="unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                            <option value="كجم">كجم (للرولات والخامات بالكيلو)</option>
                            <option value="قطعة">قطعة (للمعالق والشوك والخلة)</option>
                            <option value="بالة">بالة (للمناديل الورقية)</option>
                            <option value="كرتونة">كرتونة</option>
                            <option value="رول">رول</option>
                            <option value="زجاجة">زجاجة (للمعطر)</option>
                            <option value="ظرف">ظرف (للكاتشب)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الافتتاحي *</label>
                        <input type="number" step="0.01" min="0" name="current_stock" value="0" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الطلب الآمن (Reorder Level) *</label>
                        <input type="number" step="0.01" min="0" name="reorder_level" value="10" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التكلفة الفعلية للوحدة (ج.م) *</label>
                        <input type="number" step="0.001" min="0" name="unit_cost" value="0.00" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                        @if(!Auth::user()->isOwner() && !Auth::user()->isGM())
                            <span class="text-[10px] text-amber-700 block mt-0.5">ملاحظة: السعر يخضع لاعتماد الإدارة العليا (د. عزت وم. بولا).</span>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">مكان التخزين بالمخزن</label>
                        <input type="text" name="warehouse_location" placeholder="مثال: عنبر الرولات - رصيف 2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">مواصفات التعبئة والوزن الفعلي (Package Specs)</label>
                        <input type="text" name="package_info" placeholder="مثال: وزن الرول 116.1 كجم / 153 كرتونة × 1000 قطعة" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المورد المعتمد</label>
                        <select name="supplier_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                            <option value="">-- بدون مورد محدد --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">صورة الصنف الفعلية</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-save"></i>
                        <span>حفظ وإضافة للمخزن</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT RAW MATERIAL -->
    <div x-show="showEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    <span>تعديل بيانات الخامة: <span class="text-amber-700" x-text="editItem.name"></span></span>
                </h3>
                <button @click="showEditModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form :action="'{{ url('warehouse/raw-materials') }}/' + editItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم الخامة *</label>
                        <input type="text" name="name" x-model="editItem.name" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">التصنيف *</label>
                        <input type="text" name="category" x-model="editItem.category" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <input type="text" name="unit" x-model="editItem.unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الحالي بالمخزن *</label>
                        <input type="number" step="0.01" min="0" name="current_stock" x-model="editItem.current_stock" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الطلب الآمن *</label>
                        <input type="number" step="0.01" min="0" name="reorder_level" x-model="editItem.reorder_level" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تكلفة الوحدة (ج.م) *</label>
                        <input type="number" step="0.001" min="0" name="unit_cost" x-model="editItem.unit_cost" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500" {{ (!Auth::user()->isOwner() && !Auth::user()->isGM()) ? 'readonly' : '' }}>
                        @if(!Auth::user()->isOwner() && !Auth::user()->isGM())
                            <span class="text-[10px] text-slate-400 block mt-0.5">التسعير مقيد - خاص بالإدارة العليا</span>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الموقع بالمخزن</label>
                        <input type="text" name="warehouse_location" x-model="editItem.warehouse_location" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">مواصفات التعبئة والوزن (Package Info)</label>
                        <input type="text" name="package_info" x-model="editItem.package_info" class="w-full text-xs rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">تحديث صورة الصنف (اختياري)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showEditModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: FAST STOCK ADJUSTMENT -->
    <div x-show="showAdjustModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAdjustModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-blue-600"></i>
                    <span>تسوية رصيد جردي للصنف</span>
                </h3>
                <button @click="showAdjustModal = false" onclick="this.closest('.fixed').style.display='none'" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.adjustStock') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="item_type" value="raw_material">
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
                            <input type="number" step="0.01" min="0" name="new_stock" x-model="adjustItem.new_stock" required class="flex-1 text-sm font-black rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <span class="font-bold text-slate-600" x-text="adjustItem.unit"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سبب التسوية الجردية *</label>
                        <select name="reason" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="جرد فعلي دوري ومطابقة المخزن">جرد فعلي دوري ومطابقة المخزن</option>
                            <option value="إضافة رصيد مرحل سابق لم يقيد">إضافة رصيد مرحل سابق لم يقيد</option>
                            <option value="تسوية هالك أو تالف تشغيل">تسوية هالك أو تالف تشغيل</option>
                            <option value="تصحيح خطأ دفتري في إذن صرف">تصحيح خطأ دفتري في إذن صرف</option>
                            <option value="أخرى">أخرى (مذكورة بالملاحظات)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ملاحظات إضافية</label>
                        <textarea name="notes" rows="2" placeholder="اكتب تفاصيل الفارق أو رقم محضر الجرد..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAdjustModal = false" onclick="this.closest('.fixed').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check-double"></i>
                        <span>اعتماد التسوية وتحديث الرصيد</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
