@extends('layouts.app')

@section('title', 'تقارير المخازن وحركة الأصناف الذكية')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 text-2xl font-black">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                        نظام الاستعلام الذكي الشامل
                    </span>
                    <span class="text-xs text-slate-400">| فلترة مرنة سريعة</span>
                </div>
                <h1 class="text-2xl font-black text-slate-800 mt-1">تقارير المخازن وحركة الأصناف والخامات</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    كشوفات فورية لحركات الاستلام، الصرف، والتسليم مع المصانع (أوتوباك، بلينز باك، مصطفى قطين) والموردين والعملاء
                </p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Modal Trigger: Add Factory -->
            <button type="button" onclick="openFactoryModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white font-black text-xs shadow-md hover:bg-slate-800 transition">
                <i class="fa-solid fa-industry text-amber-400"></i>
                <span>+ إضافة مصنع جديد</span>
            </button>

            <!-- Archived Reports Link -->
            <a href="{{ route('warehouse.reports.archived') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition border border-slate-200">
                <i class="fa-solid fa-box-archive text-amber-600"></i>
                <span>أرشيف التقارير المعتمدة</span>
            </a>

            <!-- Print Current Report -->
            <a href="{{ route('warehouse.reports.print', request()->all()) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white font-black text-xs shadow-md hover:bg-amber-700 transition">
                <i class="fa-solid fa-print"></i>
                <span>طباعة التقرير واعتماده (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Unified Streamlined Filter Control Box -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 space-y-5">
        <form method="GET" action="{{ route('warehouse.reports') }}" id="warehouseReportsForm" class="space-y-4">
            
            <!-- Quick Date Presets -->
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 ml-1">فترة سريعة:</span>
                    <button type="button" onclick="setDatePreset('today')" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">اليوم</button>
                    <button type="button" onclick="setDatePreset('yesterday')" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">أمس</button>
                    <button type="button" onclick="setDatePreset('last7')" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">آخر 7 أيام</button>
                    <button type="button" onclick="setDatePreset('thisMonth')" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">هذا الشهر</button>
                    <button type="button" onclick="setDatePreset('all')" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">كافة الفترات</button>
                </div>
                <div class="text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-lg border border-amber-200">
                    <i class="fa-solid fa-filter text-amber-500 ml-1"></i>
                    <span>{{ $meta['title'] ?? 'كافة حركات المخزن' }}</span>
                </div>
            </div>

            <!-- Main Filter Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-bold text-slate-700">
                <!-- Movement Type Selector -->
                <div>
                    <label class="block text-slate-500 mb-1">نوع الحركة / التقرير المطلوب *</label>
                    <select name="report_type" id="filter_report_type" onchange="this.form.submit()" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs font-bold bg-slate-50">
                        <option value="all" {{ $reportType === 'all' ? 'selected' : '' }}>📋 كشف شامل لكافة حركات المخزن</option>
                        <option value="raw_receipt_by_item" {{ $reportType === 'raw_receipt_by_item' ? 'selected' : '' }}>📥 إجمالي استلام صنف خامات (تقرير 1)</option>
                        <option value="raw_issue_by_item" {{ $reportType === 'raw_issue_by_item' ? 'selected' : '' }}>📤 إجمالي صرف صنف خامات (تقرير 2)</option>
                        <option value="finished_receipt_production" {{ $reportType === 'finished_receipt_production' ? 'selected' : '' }}>🏭 استلام منتج تام من الإنتاج (تقرير 3)</option>
                        <option value="finished_delivery_customer" {{ $reportType === 'finished_delivery_customer' ? 'selected' : '' }}>🚚 تسليم منتج تام لعميل (س) (تقرير 4)</option>
                        <option value="raw_receipt_supplier" {{ $reportType === 'raw_receipt_supplier' ? 'selected' : '' }}>📦 استلام خامات من مورد (س) (تقرير 5)</option>
                        <option value="raw_receipt_factories" {{ $reportType === 'raw_receipt_factories' ? 'selected' : '' }}>⭐ استلام خامات من المصانع الثلاثة (تقرير 7)</option>
                        <option value="raw_issue_factories" {{ $reportType === 'raw_issue_factories' ? 'selected' : '' }}>⭐ تسليم خامات للمصانع الثلاثة (تقرير 8)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-slate-500 mb-1">من تاريخ:</label>
                    <input type="date" name="from_date" id="input_from_date" value="{{ $fromDate }}" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs font-bold">
                </div>

                <div>
                    <label class="block text-slate-500 mb-1">إلى تاريخ:</label>
                    <input type="date" name="to_date" id="input_to_date" value="{{ $toDate }}" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs font-bold">
                </div>

                <!-- Item Filter (Raw Materials OR Products) -->
                @if(in_array($reportType, ['finished_receipt_production', 'finished_delivery_customer']))
                    <div>
                        <label class="block text-slate-500 mb-1">المنتج التام (الكوڤير):</label>
                        <select name="product_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs font-bold">
                            <option value="">-- كافة المنتجات التامة --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->name }} [{{ $prod->code }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-slate-500 mb-1">صنف الخامة / المستلزم:</label>
                        <select name="raw_material_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs font-bold">
                            <option value="">-- كافة الخامات والمستلزمات --</option>
                            @foreach($rawMaterials as $mat)
                                <option value="{{ $mat->id }}" {{ $rawMaterialId == $mat->id ? 'selected' : '' }}>
                                    {{ $mat->name }} [{{ $mat->code }}] ({{ $mat->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Second Row Filters: Supplier / Customer / Factory (Contextual) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-bold text-slate-700 pt-2 border-t border-slate-100">
                <!-- Supplier Filter -->
                <div>
                    <label class="block text-slate-500 mb-1">المورد:</label>
                    <select name="supplier_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة الموردين --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Filter -->
                <div>
                    <label class="block text-slate-500 mb-1">العميل:</label>
                    <select name="customer_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة العملاء --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $customerId == $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Factory Filter -->
                <div>
                    <label class="block text-slate-500 mb-1">المصنع الشريك / الداخلي:</label>
                    <select name="factory_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة المصانع --</option>
                        @foreach($factories as $fac)
                            <option value="{{ $fac->id }}" {{ $factoryId == $fac->id ? 'selected' : '' }}>
                                {{ $fac->id <= 3 ? '⭐ ' : '' }}{{ $fac->name }} ({{ $fac->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <span class="text-xs text-slate-400">
                    يمكنك التصفية بأكثر من معيار في آن واحد والطباعة الفورية
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('warehouse.reports', ['report_type' => 'all']) }}" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs transition">
                        إعادة ضبط الفلاتر
                    </a>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-amber-600 text-white font-black text-xs shadow-md hover:bg-amber-700 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>تطبيق الفلترة والبحث</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-400 mb-1">إجمالي كمية الحركة</span>
                <span class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalQuantity, 2) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">وحدة / قطعة / كجم</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-400 mb-1">إجمالي القيمة التقديرية</span>
                <span class="text-2xl font-black text-emerald-600 font-mono">{{ number_format($totalValue, 2) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">جنيه مصري (ج.م)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-400 mb-1">عدد بنود وسجلات الحركة</span>
                <span class="text-2xl font-black text-slate-800 font-mono">{{ number_format($itemsCount) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">سجل حركة مطابق للبحث</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Data Table with Item Photos -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-slate-800">بيانات التقرير وحركات الأصناف التفصيلية</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    الفترة من <span class="font-mono font-bold text-slate-700">{{ $fromDate }}</span> إلى <span class="font-mono font-bold text-slate-700">{{ $toDate }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('warehouse.reports.print', request()->all()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-black text-xs hover:bg-slate-800 transition shadow-sm inline-flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-amber-400"></i>
                    <span>معاينة الطباعة والتوقيعات</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                        <th class="py-3 px-3 text-center">#</th>
                        <th class="py-3 px-3 w-14">صورة الصنف</th>
                        <th class="py-3 px-3">تاريخ ورقم الإذن</th>
                        <th class="py-3 px-3">نوع الحركة</th>
                        <th class="py-3 px-3">الصنف / الخامة والتوصيف</th>
                        <th class="py-3 px-3 text-center">الكمية</th>
                        <th class="py-3 px-3 text-center">الوحدة</th>
                        <th class="py-3 px-3 text-left font-mono">سعر الوحدة</th>
                        <th class="py-3 px-3 text-left font-mono">القيمة الإجمالية</th>
                        <th class="py-3 px-3">الطرف المتعامل</th>
                        <th class="py-3 px-3">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $idx => $mItem)
                    @php
                        $img = $mItem->image_url;
                    @endphp
                    <tr class="hover:bg-amber-50/20 transition">
                        <td class="py-3 px-3 text-center text-slate-400 font-mono">{{ $movements->firstItem() + $idx }}</td>
                        
                        <!-- Item Image Thumbnail -->
                        <td class="py-3 px-3">
                            <img src="{{ $img }}" 
                                 alt="{{ $mItem->item_name }}" 
                                 class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:scale-105 transition bg-white flex-shrink-0"
                                 onclick="openReportImageZoom('{{ $img }}', '{{ addslashes($mItem->item_name) }}')">
                        </td>

                        <td class="py-3 px-3">
                            <a href="{{ route('warehouse.vouchers.show', $mItem->stockMovement?->id ?? 1) }}" target="_blank" class="font-mono font-bold text-amber-700 hover:underline block">
                                {{ $mItem->stockMovement?->voucher_number }}
                            </a>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $mItem->stockMovement?->voucher_date?->format('Y-m-d') }}</span>
                        </td>

                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ str_contains($mItem->stockMovement?->type ?? '', 'receipt') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $mItem->stockMovement?->type_arabic }}
                            </span>
                        </td>

                        <td class="py-3 px-3 font-bold text-slate-800">
                            <div class="text-sm font-black text-slate-900">{{ $mItem->item_name }}</div>
                            <span class="block text-[10px] text-slate-400">
                                {{ $mItem->item_type === 'raw_material' ? 'مادة خام' : 'منتج تام كوڤير' }}
                                @if($mItem->rawMaterial?->code || $mItem->product?->code)
                                    | كود: {{ $mItem->rawMaterial?->code ?? $mItem->product?->code }}
                                @endif
                            </span>
                        </td>

                        <td class="py-3 px-3 text-center font-mono font-black text-slate-900">{{ number_format($mItem->quantity, 2) }}</td>
                        <td class="py-3 px-3 text-center text-slate-600 font-bold">{{ $mItem->unit }}</td>
                        <td class="py-3 px-3 text-left font-mono text-slate-700">{{ number_format($mItem->unit_cost, 2) }} ج.م</td>
                        <td class="py-3 px-3 text-left font-mono font-bold text-emerald-700">{{ number_format($mItem->total_cost, 2) }} ج.م</td>
                        <td class="py-3 px-3 font-bold text-slate-700">
                            {{ $mItem->stockMovement?->party_display_name }}
                        </td>
                        <td class="py-3 px-3 text-[11px] text-slate-500 max-w-xs truncate">
                            {{ $mItem->notes ?? $mItem->stockMovement?->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2 block text-slate-300"></i>
                            <span>لا توجد حركات مخزنية مطابقة لمعايير البحث في هذه الفترة.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($movements->count() > 0)
                <tfoot class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                    <tr>
                        <td colspan="5" class="py-3 px-4 text-left font-black">إجمالي الصفحة الحالية:</td>
                        <td class="py-3 px-4 text-center font-mono font-black text-amber-700">{{ number_format($movements->sum('quantity'), 2) }}</td>
                        <td></td>
                        <td></td>
                        <td class="py-3 px-4 text-left font-mono font-black text-emerald-700">{{ number_format($movements->sum('total_cost'), 2) }} ج.م</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Modal: Add New Factory (Dynamic factory addition) -->
<div id="factoryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-industry"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">إضافة مصنع جديد للنظام</h3>
                    <p class="text-xs text-slate-400">تثبيت المصانع الثلاثة (أوتوباك، بلينز باك، مصطفى قطين) مع إمكانية التوسع</p>
                </div>
            </div>
            <button onclick="closeFactoryModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('warehouse.factories.store') }}" method="POST" class="space-y-4 text-xs font-bold text-slate-700">
            @csrf
            <div>
                <label class="block text-slate-500 mb-1">اسم المصنع *</label>
                <input type="text" name="name" required placeholder="مثال: مصنع الأمل للتغليف الحديث" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs font-bold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-500 mb-1">كود المصنع *</label>
                    <input type="text" name="code" required placeholder="مثال: FAC-04" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs font-mono font-bold">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">نوع المصنع *</label>
                    <select name="type" required class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs font-bold">
                        <option value="internal">مصنع / خط داخلي (+14%)</option>
                        <option value="outsourced">مصنع تشغيل لدى الغير (Outsourced)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-500 mb-1">المشرف / مسؤول المصنع</label>
                    <input type="text" name="supervisor_name" placeholder="اسم المهندس أو الفني" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">تكلفة التشغيل للساعة (ج.م)</label>
                    <input type="number" step="0.01" name="operating_cost_per_hour" value="0" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-slate-500 mb-1">ملاحظات / وصف خطوط الإنتاج</label>
                <textarea name="description" rows="2" placeholder="وصف الماكينات والتخصص..." class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeFactoryModal()" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    إلغاء
                </button>
                <button type="submit" class="px-6 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md hover:bg-slate-800 transition">
                    حفظ وتثبيت المصنع
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox Modal for Report Images -->
<div id="reportZoomModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeReportImageZoom()">
    <div class="bg-white rounded-3xl max-w-lg w-full p-4 space-y-3 overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h4 id="reportZoomTitle" class="font-black text-slate-900 text-sm"></h4>
            <button type="button" onclick="closeReportImageZoom()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="rounded-2xl overflow-hidden bg-slate-100 flex items-center justify-center max-h-[70vh]">
            <img id="reportZoomImg" src="" alt="Zoom" class="max-h-[65vh] w-auto object-contain">
        </div>
    </div>
</div>

<script>
    function openFactoryModal() {
        const modal = document.getElementById('factoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeFactoryModal() {
        const modal = document.getElementById('factoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openReportImageZoom(url, title) {
        document.getElementById('reportZoomImg').src = url;
        document.getElementById('reportZoomTitle').textContent = title;
        document.getElementById('reportZoomModal').classList.remove('hidden');
    }

    function closeReportImageZoom() {
        document.getElementById('reportZoomModal').classList.add('hidden');
    }

    function setDatePreset(preset) {
        const fromInput = document.getElementById('input_from_date');
        const toInput = document.getElementById('input_to_date');
        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (preset === 'today') {
            fromInput.value = formatDate(today);
            toInput.value = formatDate(today);
        } else if (preset === 'yesterday') {
            const y = new Date();
            y.setDate(y.getDate() - 1);
            fromInput.value = formatDate(y);
            toInput.value = formatDate(y);
        } else if (preset === 'last7') {
            const d7 = new Date();
            d7.setDate(d7.getDate() - 7);
            fromInput.value = formatDate(d7);
            toInput.value = formatDate(today);
        } else if (preset === 'thisMonth') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            fromInput.value = formatDate(firstDay);
            toInput.value = formatDate(today);
        } else if (preset === 'all') {
            fromInput.value = '2020-01-01';
            toInput.value = formatDate(today);
        }

        document.getElementById('warehouseReportsForm').submit();
    }
</script>
@endsection
