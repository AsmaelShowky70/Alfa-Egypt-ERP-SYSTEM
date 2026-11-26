@extends('layouts.app')

@section('title', 'نواقص المخازن وتنبيهات إعادة الطلب - المشتريات')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>نواقص المخازن وتنبيهات إعادة الطلب الفوري</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">متابعة دقيقة للأصناف والخامات التي وصلت إلى حد الخطر أو رصيد صفر لتوليد أوامر الشراء للموردين</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchasing.orders.create') }}" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-cart-plus"></i>
                <span>إنشاء أمر شراء جديد</span>
            </a>
            <a href="{{ route('purchasing.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                <span>← لوحة المشتريات</span>
            </a>
        </div>
    </div>

    <!-- Alert Banner & KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-rose-500 to-rose-700 text-white p-5 rounded-3xl shadow-sm space-y-1">
            <div class="flex items-center justify-between text-rose-100 text-xs font-medium">
                <span>إجمالي الأصناف الحرجة</span>
                <i class="fa-solid fa-bell text-rose-200"></i>
            </div>
            <div class="text-3xl font-black">{{ $totalShortagesCount }}</div>
            <p class="text-[11px] text-rose-100">تحت حد الأمان أو منعدمة الرصيد</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>خامات ومستلزمات ناقصة</span>
                <i class="fa-solid fa-cubes-stacked text-amber-500"></i>
            </div>
            <div class="text-3xl font-black text-amber-600">{{ $shortageMaterials->count() }}</div>
            <p class="text-[11px] text-slate-400">سيلوفان، بلاستيك، كراتين، مناديل</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>منتجات تامة منعدمة / ناقصة</span>
                <i class="fa-solid fa-box-open text-purple-600"></i>
            </div>
            <div class="text-3xl font-black text-purple-700">{{ $shortageProducts->count() }}</div>
            <p class="text-[11px] text-slate-400">كوڤير جاهز للعملاء للتسليم</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>الموردون المتاحون للتوريد</span>
                <i class="fa-solid fa-handshake text-emerald-600"></i>
            </div>
            <div class="text-3xl font-black text-emerald-700">{{ $suppliers->count() }}</div>
            <p class="text-[11px] text-slate-400">سلاسل إمداد نشطة ومعتمدة</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('purchasing.warehouseShortages') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث باسم أو كود الصنف</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث مثلاً: سيلوفان، معلقة، أبو طارق..." class="w-full text-xs rounded-xl border-slate-200 pr-8 focus:ring-purple-500 focus:border-purple-500">
                    <i class="fa-solid fa-search absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">تصنيف الخامة</label>
                <select name="category" class="w-full text-xs rounded-xl border-slate-200 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- كافة التصنيفات --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية النواقص</span>
                </button>
                <a href="{{ route('purchasing.warehouseShortages') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إعادة ضبط
                </a>
            </div>
        </form>
    </div>

    <!-- Section 1: Raw Materials Shortages -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                <span>نواقص المواد الخام والمستلزمات (تنبيهات الشراء)</span>
            </h2>
            <span class="text-xs bg-rose-50 text-rose-700 font-bold px-3 py-1 rounded-full border border-rose-200">
                {{ $shortageMaterials->count() }} صنف يحتاج توريد
            </span>
        </div>

        @if($shortageMaterials->isEmpty())
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <i class="fa-solid fa-circle-check text-4xl text-emerald-500 mb-2"></i>
                <p class="text-sm font-bold text-slate-700">لا توجد نواقص في المواد الخام حالياً، كافة الأرصدة فوق حد الأمان!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <th class="py-3 px-3">صورة الصنف</th>
                            <th class="py-3 px-3">كود الخامة</th>
                            <th class="py-3 px-3">اسم الخامة والمواصفات</th>
                            <th class="py-3 px-3">التصنيف</th>
                            <th class="py-3 px-3">الرصيد الفعلي الحالي</th>
                            <th class="py-3 px-3">حد الطلب الآمن</th>
                            <th class="py-3 px-3">كمية العجز المقدرة</th>
                            <th class="py-3 px-3">المورد المعتمد</th>
                            <th class="py-3 px-3 text-center">إجراء سريع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($shortageMaterials as $rm)
                            @php
                                $deficit = max(0, $rm->reorder_level - $rm->current_stock);
                                $isZero = $rm->current_stock <= 0;
                            @endphp
                            <tr class="hover:bg-rose-50/40 transition {{ $isZero ? 'bg-rose-50/20' : '' }}">
                                <td class="py-2.5 px-3">
                                    <img src="{{ $rm->image_url }}" alt="{{ $rm->name }}" class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-sm bg-white">
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-700">{{ $rm->code }}</td>
                                <td class="py-2.5 px-3">
                                    <div class="font-black text-slate-900 text-sm">{{ $rm->name }}</div>
                                    @if($rm->package_info)
                                        <div class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-box text-slate-400 ml-1"></i>{{ $rm->package_info }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $rm->category }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-black {{ $isZero ? 'text-rose-600' : 'text-amber-600' }}">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm">{{ number_format($rm->current_stock, 2) }}</span>
                                        <span class="text-[11px] font-normal text-slate-500">{{ $rm->unit }}</span>
                                    </div>
                                    @if($isZero)
                                        <span class="inline-block mt-0.5 px-1.5 py-0.5 text-[9px] font-black bg-rose-600 text-white rounded">رصيد صفري تماماً</span>
                                    @else
                                        <span class="inline-block mt-0.5 px-1.5 py-0.5 text-[9px] font-black bg-amber-100 text-amber-800 rounded">تحت حد الأمان</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 font-bold text-slate-600">
                                    {{ number_format($rm->reorder_level) }} {{ $rm->unit }}
                                </td>
                                <td class="py-2.5 px-3 font-black text-rose-700">
                                    + {{ number_format($deficit > 0 ? $deficit : $rm->reorder_level) }} {{ $rm->unit }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    {{ $rm->supplier ? $rm->supplier->name : 'غير محدد' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('purchasing.orders.create', ['supplier_id' => $rm->supplier_id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span>طلب شراء</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section 2: Finished Goods Shortages -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                <span>نواقص المنتجات التامة (كوڤير، عبوات جاهزة للتسليم)</span>
            </h2>
            <span class="text-xs bg-purple-50 text-purple-700 font-bold px-3 py-1 rounded-full border border-purple-200">
                {{ $shortageProducts->count() }} صنف منتج تام
            </span>
        </div>

        @if($shortageProducts->isEmpty())
            <div class="p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-500 mb-1"></i>
                <p class="text-xs font-bold text-slate-600">مخزن المنتج التام يحتوي على كميات كافية ولا توجد عواجز حرجة.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <th class="py-3 px-3">الصورة</th>
                            <th class="py-3 px-3">كود المنتج</th>
                            <th class="py-3 px-3">اسم المنتج والمواصفة</th>
                            <th class="py-3 px-3">العميل المرتبط</th>
                            <th class="py-3 px-3">الرصيد التام بالمخزن</th>
                            <th class="py-3 px-3">حد الأمان</th>
                            <th class="py-3 px-3">تكلفة القطعة (BOM)</th>
                            <th class="py-3 px-3 text-center">إشعار التشغيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($shortageProducts as $fg)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 px-3">
                                    <img src="{{ $fg->image_url }}" alt="{{ $fg->name }}" class="w-12 h-12 object-cover rounded-xl border border-slate-200 bg-white">
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-700">{{ $fg->code }}</td>
                                <td class="py-2.5 px-3">
                                    <div class="font-black text-slate-900 text-sm">{{ $fg->name }}</div>
                                    @if($fg->package_info)
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $fg->package_info }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-slate-700 font-bold">
                                    {{ $fg->customer ? $fg->customer->name : 'متاح للجميع / عام' }}
                                </td>
                                <td class="py-2.5 px-3 font-black text-rose-600">
                                    {{ number_format($fg->current_stock) }} {{ $fg->unit }}
                                    @if($fg->current_stock <= 0)
                                        <span class="block text-[9px] text-rose-500">رصيد صفر (مطلوب أمر تشغيل)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-slate-500">{{ number_format($fg->reorder_level) }} {{ $fg->unit }}</td>
                                <td class="py-2.5 px-3 font-black text-slate-800">{{ number_format($fg->cost_price, 2) }} ج.م</td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('operations.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                                        <i class="fa-solid fa-cogs"></i>
                                        <span>إشعار التشغيل</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
