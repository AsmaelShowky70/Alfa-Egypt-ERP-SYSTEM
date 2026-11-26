@extends('layouts.app')

@section('title', 'أرصدة خامات ومخازن الشركة (عرض لحظي للتشغيل)')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[11px] font-black rounded-lg bg-blue-100 text-blue-800 border border-blue-200">
                    <i class="fa-solid fa-eye ml-1"></i> وضع العرض اللحظي فقط (Read-Only)
                </span>
                <span class="text-xs text-slate-400">| مستودع البيانات المركزي</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2 mt-1">
                <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                <span>أرصدة خامات ومستلزمات المخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">متابعة دقيقة للأرصدة المتوفرة (كجم، بالة، كرتونة، رول، قطعة) ومواصفات الطرود لتخطيط تشغيل الماكينات وأوامر الإنتاج</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('operations.requisitions') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>طلبات صرف الخامات</span>
            </a>
            <a href="{{ route('operations.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <span>← لوحة التشغيل</span>
            </a>
        </div>
    </div>

    <!-- Quick Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-5 rounded-3xl shadow-sm space-y-1">
            <div class="flex items-center justify-between text-blue-100 text-xs font-medium">
                <span>إجمالي أصناف الخامات</span>
                <i class="fa-solid fa-cubes-stacked text-blue-200"></i>
            </div>
            <div class="text-3xl font-black">{{ $totalRawMaterialsCount }}</div>
            <p class="text-[11px] text-blue-100">رولات، بلاستيكيات، مناديل، بهارات</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>إجمالي القيمة المخزنية</span>
                <i class="fa-solid fa-coins text-emerald-600"></i>
            </div>
            <div class="text-2xl font-black text-emerald-700">{{ number_format($totalStockValue, 2) }} ج.م</div>
            <p class="text-[11px] text-slate-400">تقييم الخامات الفعلي المتاح بالمصنع</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>أصناف جاهزة للتشغيل الفوري</span>
                <i class="fa-solid fa-check-circle text-blue-600"></i>
            </div>
            <div class="text-3xl font-black text-blue-700">{{ $rawMaterials->where('current_stock', '>', 0)->count() }}</div>
            <p class="text-[11px] text-slate-400">رصيد متاح في هذه الصفحة</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>المنتجات التامة بالمخزن</span>
                <i class="fa-solid fa-box-open text-purple-600"></i>
            </div>
            <div class="text-3xl font-black text-purple-700">{{ $finishedGoods->count() }}</div>
            <p class="text-[11px] text-slate-400">كوڤير جاهز للتسليم</p>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('operations.warehouseStock') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث باسم أو كود أو مواصفة</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="مثال: رول، معلقة، 116، كرتونة..." class="w-full text-xs rounded-xl border-slate-200 pr-8 focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-search absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">تصنيف الخامة</label>
                <select name="category" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- كافة التصنيفات --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">حالة الرصيد</label>
                <select name="stock_filter" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- كافة الحالات --</option>
                    <option value="available" {{ request('stock_filter') == 'available' ? 'selected' : '' }}>متوفر فقط (> 0)</option>
                    <option value="low" {{ request('stock_filter') == 'low' ? 'selected' : '' }}>تحت حد الأمان</option>
                    <option value="zero" {{ request('stock_filter') == 'zero' ? 'selected' : '' }}>رصيد صفري تماماً</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية الأرصدة</span>
                </button>
                <a href="{{ route('operations.warehouseStock') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Raw Materials Table (Strictly Read-Only with Material Requisition CTA) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-blue-600"></i>
                    <span>رصيد المواد الخام ومستلزمات الإنتاج الفعلي</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">شاشة استعلامية لمهندسي وفنيي التشغيل لاختيار وتخطيط الخامات بدون تعديل على المخزن</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    عرض {{ $rawMaterials->count() }} من أصل {{ $rawMaterials->total() }} خامة
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-3">صورة الصنف</th>
                        <th class="py-3 px-3">كود الخامة</th>
                        <th class="py-3 px-3">اسم الخامة ومواصفات الطرد</th>
                        <th class="py-3 px-3">التصنيف</th>
                        <th class="py-3 px-3">الرصيد المتاح الحالي</th>
                        <th class="py-3 px-3">الوحدة</th>
                        <th class="py-3 px-3">حد الأمان</th>
                        <th class="py-3 px-3">الموقع بالمخزن</th>
                        <th class="py-3 px-3 text-center">طلب صرف للتشغيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rawMaterials as $rm)
                        @php
                            $isZero = $rm->current_stock <= 0;
                            $isLow = $rm->current_stock <= $rm->reorder_level && !$isZero;
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition {{ $isZero ? 'bg-rose-50/20' : '' }}">
                            <td class="py-2.5 px-3">
                                <img src="{{ $rm->image_url }}" alt="{{ $rm->name }}" class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-sm bg-white" loading="lazy">
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-600">{{ $rm->code }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-black text-slate-900 text-sm">{{ $rm->name }}</div>
                                @if($rm->package_info)
                                    <div class="text-[11px] text-blue-700 font-medium mt-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-boxes-packing text-blue-500"></i>
                                        <span>{{ $rm->package_info }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="inline-block px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $rm->category }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="text-base font-black {{ $isZero ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900') }}">
                                    {{ number_format($rm->current_stock, 2) }}
                                </span>
                                @if($isZero)
                                    <span class="inline-block mr-1 text-[9px] font-bold bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded">غير متوفر</span>
                                @elseif($isLow)
                                    <span class="inline-block mr-1 text-[9px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">منخفض</span>
                                @else
                                    <span class="inline-block mr-1 text-[9px] font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">متاح</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-600">{{ $rm->unit }}</td>
                            <td class="py-2.5 px-3 text-slate-500 font-mono">{{ number_format($rm->reorder_level) }} {{ $rm->unit }}</td>
                            <td class="py-2.5 px-3 text-slate-500">{{ $rm->warehouse_location ?? 'عنبر المخزن الرئيسي' }}</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($rm->current_stock > 0)
                                    <a href="{{ route('operations.requisitions') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition">
                                        <i class="fa-solid fa-dolly"></i>
                                        <span>طلب صرف</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-slate-400 bg-slate-100 rounded-lg cursor-not-allowed">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>رصيد غير كافٍ</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                لا توجد خامات مطابقة لشروط البحث والتصفية.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $rawMaterials->links() }}
        </div>
    </div>

    <!-- Finished Goods Live Overview (Read-Only) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-purple-600"></i>
                    <span>جاهزية المنتج التام بالمخزن (لأوامر التسليم)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">كميات الكوڤير والمنتجات الجاهزة والمسجلة بالمخزن</p>
            </div>
            <span class="text-xs font-bold text-purple-700 bg-purple-50 px-3 py-1 rounded-full border border-purple-200">
                {{ $finishedGoods->count() }} صنف منتج تام
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($finishedGoods as $fg)
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="font-mono text-[10px] text-slate-400 font-bold">{{ $fg->code }}</span>
                            <div class="font-black text-sm text-slate-900">{{ $fg->name }}</div>
                            @if($fg->customer)
                                <div class="text-[11px] text-blue-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>عميل: {{ $fg->customer->name }}</span>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-500 font-bold">صنف عام للبيع المباشر</div>
                            @endif
                        </div>
                        <img src="{{ $fg->image_url }}" alt="{{ $fg->name }}" class="w-14 h-14 object-cover rounded-xl border border-slate-200 bg-white shadow-sm">
                    </div>

                    @if($fg->package_info)
                        <div class="text-[11px] text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                            {{ $fg->package_info }}
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
                        <span class="text-slate-500">الرصيد التام بالمخزن:</span>
                        <span class="font-black text-sm {{ $fg->current_stock > 0 ? 'text-purple-700' : 'text-rose-600' }}">
                            {{ number_format($fg->current_stock) }} {{ $fg->unit }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
