@extends('layouts.app')

@section('title', 'تقارير المخازن وحركة الأصناف - الفا ايجبت')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 text-2xl font-black">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800">تقارير المخازن وحركة الأصناف</h1>
                <p class="text-xs text-slate-500 mt-1">
                    التقارير اليومية والدورية المعتمدة للأصناف والمواد الخام والمصانع (أوتوباك، بلينز باك، مصطفى قطين) قابلة للطباعة والتوقيع والأرشفة
                </p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Modal Trigger: Add Factory -->
            <button type="button" onclick="openFactoryModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md hover:bg-slate-800 transition">
                <i class="fa-solid fa-industry"></i>
                <span>إضافة مصنع جديد</span>
            </button>

            <!-- Archived Reports Link -->
            <a href="{{ route('warehouse.reports.archived') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition border border-slate-200">
                <i class="fa-solid fa-box-archive text-amber-600"></i>
                <span>أرشيف التقارير المعتمدة</span>
            </a>

            <!-- Print Current Report -->
            <a href="{{ route('warehouse.reports.print', request()->all()) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md hover:bg-amber-700 transition">
                <i class="fa-solid fa-print"></i>
                <span>طباعة التقرير واعتماده (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Quick Report Navigation Tabs (8 Specific Reports from Management Request) -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-500 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-list-check text-amber-500"></i>
            <span>التقارير المعتمدة المطلوبة لإدارة المخازن (اختر التقرير المطلوب لتطبيق معاييره فوراً):</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-2 text-xs">
            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'all'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'all' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <i class="fa-solid fa-layer-group block mb-1 text-base"></i>
                <span>تقرير شامل لكافة الحركات</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'raw_receipt_by_item'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'raw_receipt_by_item' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (1)</span>
                <span>إجمالي استلام صنف خامات</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'raw_issue_by_item'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'raw_issue_by_item' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (2)</span>
                <span>إجمالي صرف صنف خامات</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'finished_receipt_production'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'finished_receipt_production' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (3)</span>
                <span>استلام منتج تام من الإنتاج</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'finished_delivery_customer'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'finished_delivery_customer' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (4)</span>
                <span>تسليم منتج تام لعميل (س)</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'raw_receipt_supplier'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'raw_receipt_supplier' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (5)</span>
                <span>استلام خامات من مورد (س)</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'raw_receipt_factories'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'raw_receipt_factories' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (7)</span>
                <span>استلام خامات من المصانع الثلاثة</span>
            </a>

            <a href="{{ route('warehouse.reports', array_merge(request()->except(['report_type', 'page']), ['report_type' => 'raw_issue_factories'])) }}" 
               class="p-3 rounded-xl border text-center font-bold transition {{ $reportType === 'raw_issue_factories' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' }}">
                <span class="block text-[10px] opacity-75">تقرير (8)</span>
                <span>تسليم خامات للمصانع الثلاثة</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('warehouse.reports') }}" class="space-y-4">
            <input type="hidden" name="report_type" value="{{ $reportType }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-bold text-slate-700">
                <!-- From Date -->
                <div>
                    <label class="block text-slate-500 mb-1">من تاريخ:</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-slate-500 mb-1">إلى تاريخ:</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                </div>

                <!-- Raw Material Filter -->
                @if(in_array($reportType, ['all', 'raw_receipt_by_item', 'raw_issue_by_item', 'raw_receipt_supplier', 'raw_receipt_factories', 'raw_issue_factories']))
                <div>
                    <label class="block text-slate-500 mb-1">صنف الخامة:</label>
                    <select name="raw_material_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة الخامات --</option>
                        @foreach($rawMaterials as $mat)
                            <option value="{{ $mat->id }}" {{ $rawMaterialId == $mat->id ? 'selected' : '' }}>
                                {{ $mat->name }} (كود: {{ $mat->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Product Filter -->
                @if(in_array($reportType, ['all', 'finished_receipt_production', 'finished_delivery_customer']))
                <div>
                    <label class="block text-slate-500 mb-1">المنتج التام (الكوڤير):</label>
                    <select name="product_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة المنتجات التامة --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} [{{ $prod->customer?->name ?? 'عام' }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Supplier Filter -->
                @if(in_array($reportType, ['all', 'raw_receipt_supplier']))
                <div>
                    <label class="block text-slate-500 mb-1">المورد (س):</label>
                    <select name="supplier_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة الموردين --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Customer Filter -->
                @if(in_array($reportType, ['all', 'finished_delivery_customer']))
                <div>
                    <label class="block text-slate-500 mb-1">العميل (س):</label>
                    <select name="customer_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة العملاء --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $customerId == $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Factory Filter (Includes Pinned 3 Factories: AutoPack, PlainsPack, Mostafa Qattin) -->
                @if(in_array($reportType, ['all', 'raw_receipt_factories', 'raw_issue_factories']))
                <div>
                    <label class="block text-slate-500 mb-1">المصنع (المصانع الرئيسية الثلاثة):</label>
                    <select name="factory_id" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-xs">
                        <option value="">-- كافة المصانع الثلاثة --</option>
                        @foreach($factories as $fac)
                            <option value="{{ $fac->id }}" {{ $factoryId == $fac->id ? 'selected' : '' }}>
                                {{ $fac->id <= 3 ? '⭐ ' : '' }}{{ $fac->name }} ({{ $fac->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-500">
                    التقرير الحالي: <strong class="text-slate-800">{{ $meta['title'] }}</strong>
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('warehouse.reports', ['report_type' => $reportType]) }}" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                        إعادة ضبط
                    </a>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md hover:bg-amber-700 transition">
                        <i class="fa-solid fa-filter ml-1"></i>
                        تطبيق الفلترة
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

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-800">بيانات التقرير التفصيلية</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    الفترة من <span class="font-mono font-bold text-slate-700">{{ $fromDate }}</span> إلى <span class="font-mono font-bold text-slate-700">{{ $toDate }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('warehouse.reports.print', request()->all()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition shadow-sm inline-flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-red-400"></i>
                    <span>معاينة الطباعة وتوثيق التوقيعات</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-3 px-4 text-center">#</th>
                        <th class="py-3 px-4">تاريخ السند</th>
                        <th class="py-3 px-4">رقم الإذن / السند</th>
                        <th class="py-3 px-4">نوع الحركة</th>
                        <th class="py-3 px-4">الصنف / الخامة</th>
                        <th class="py-3 px-4 text-center">الكمية</th>
                        <th class="py-3 px-4 text-center">الوحدة</th>
                        <th class="py-3 px-4 text-left font-mono">سعر الوحدة</th>
                        <th class="py-3 px-4 text-left font-mono">القيمة الإجمالية</th>
                        <th class="py-3 px-4">الجهة المسلمة / المستلمة</th>
                        <th class="py-3 px-4">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $idx => $mItem)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $movements->firstItem() + $idx }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $mItem->stockMovement?->voucher_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-amber-700">
                            <a href="{{ route('warehouse.vouchers.show', $mItem->stockMovement?->id ?? 1) }}" target="_blank" class="hover:underline">
                                {{ $mItem->stockMovement?->voucher_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ str_contains($mItem->stockMovement?->type ?? '', 'receipt') ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $mItem->stockMovement?->type_arabic }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $mItem->item_name }}
                            <span class="block text-[10px] text-slate-400">{{ $mItem->item_type == 'raw_material' ? 'مادة خام' : 'منتج تام كوڤير' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-black text-slate-900">{{ number_format($mItem->quantity, 2) }}</td>
                        <td class="py-3 px-4 text-center text-slate-600">{{ $mItem->unit }}</td>
                        <td class="py-3 px-4 text-left font-mono text-slate-700">{{ number_format($mItem->unit_cost, 2) }} ج.م</td>
                        <td class="py-3 px-4 text-left font-mono font-bold text-slate-900">{{ number_format($mItem->total_cost, 2) }} ج.م</td>
                        <td class="py-3 px-4 font-bold text-slate-700">
                            {{ $mItem->stockMovement?->party_display_name }}
                        </td>
                        <td class="py-3 px-4 text-[11px] text-slate-500 max-w-xs truncate">
                            {{ $mItem->notes ?? $mItem->stockMovement?->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                            <span>لا توجد حركات مطابقة لمعايير البحث في هذه الفترة.</span>
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
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-slate-100 animate-in fade-in zoom-in duration-200">
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
                <input type="text" name="name" required placeholder="مثال: مصنع الأمل للتغليف الحديث" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-500 mb-1">كود المصنع *</label>
                    <input type="text" name="code" required placeholder="مثال: FAC-04" class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">نوع المصنع *</label>
                    <select name="type" required class="w-full rounded-xl border-slate-300 focus:border-amber-500 text-xs">
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
</script>
@endsection
