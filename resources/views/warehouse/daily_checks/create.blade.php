@extends('layouts.app')

@section('title', 'تسجيل محضر جرد يومي ذكي - إدارة المخازن')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 text-2xl font-black">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                        نظام الجرد اللحظي الذكي
                    </span>
                    <span class="text-xs text-slate-400">| مطابقة الأرصدة بالصور</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-1">تسجيل محضر الجرد الفعلي للمخزن المركزي</h1>
                <p class="text-xs text-slate-500 mt-0.5">مطابقة فورية بالصور والأرصدة الدفتريّة، احتساب آلي لفروق الكميات والأثر المالي (فائض/عجز)</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.dailyChecks') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>محاضر الجرد السابقة</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition">
                لوحة المخازن
            </a>
        </div>
    </div>

    <!-- Category Selector Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 ml-1">فئة الجرد:</span>
            <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'raw_materials']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $category === 'raw_materials' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i class="fa-solid fa-cubes-stacked"></i>
                <span>المواد الخام ومستلزمات الإنتاج ({{ $category === 'raw_materials' ? $items->count() : '...' }})</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'finished_goods']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $category === 'finished_goods' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i class="fa-solid fa-box-open"></i>
                <span>المنتج التام والكوڤير ({{ $category === 'finished_goods' ? $items->count() : '...' }})</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks.create', ['category' => 'spare_parts']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $category === 'spare_parts' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i class="fa-solid fa-wrench"></i>
                <span>قطع الغيار والعدد ({{ $category === 'spare_parts' ? $items->count() : '...' }})</span>
            </a>
        </div>

        <!-- Quick Fill Helpers -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="autoMatchAll()" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-xs" title="تعبئة كافة الكميات الفعلية لتطابق الرصيد الدفتري فوراً">
                <i class="fa-solid fa-check-double text-emerald-600"></i>
                <span>مطابقة كافة الأرصدة (الفعلي = الدفتري)</span>
            </button>
            <button type="button" onclick="resetAllActuals()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="تصفير خانات الإدخال الفعلي">
                <i class="fa-solid fa-rotate-left"></i>
                <span>تصفير</span>
            </button>
        </div>
    </div>

    <!-- Live Reactive Financial & Counting Summary KPI Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 block">إجمالي بنود الجرد</span>
            <b id="kpi_total_items" class="text-xl font-black text-slate-900 font-mono">{{ $items->count() }}</b>
            <span class="text-[10px] text-slate-500 block">صنف مدرج</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 block">الأصناف المطابقة</span>
            <b id="kpi_matched_items" class="text-xl font-black text-blue-600 font-mono">0</b>
            <span class="text-[10px] text-blue-500 block">بدون أي فروق</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-0.5">
            <span class="text-[11px] font-bold text-emerald-700 block">أصناف الفائض (+)</span>
            <b id="kpi_surplus_value" class="text-xl font-black text-emerald-600 font-mono">+0.00 ج.م</b>
            <span id="kpi_surplus_count" class="text-[10px] text-emerald-600 block">0 أصناف</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-0.5">
            <span class="text-[11px] font-bold text-rose-700 block">أصناف العجز (-)</span>
            <b id="kpi_deficit_value" class="text-xl font-black text-rose-600 font-mono">-0.00 ج.م</b>
            <span id="kpi_deficit_count" class="text-[10px] text-rose-600 block">0 أصناف</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center space-y-0.5 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-slate-400 block">صافي الأثر المالي</span>
            <b id="kpi_net_impact" class="text-xl font-black text-slate-900 font-mono">0.00 ج.م</b>
            <span id="kpi_net_badge" class="text-[10px] font-bold text-slate-500 block">مطابق</span>
        </div>
    </div>

    <!-- Live Search & Category Filtering Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1">
                <input type="text" id="inventorySearchInput" oninput="filterInventoryRows()" placeholder="بحث فوري في قائمة الجرد باسم الصنف، الكود، أو مواصفات الطرد..." class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                <i class="fa-solid fa-search absolute right-3.5 top-3 text-slate-400 text-xs"></i>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-bold whitespace-nowrap">عرض:</span>
                <span id="visibleRowsCount" class="text-xs font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg">
                    {{ $items->count() }} صنف
                </span>
            </div>
        </div>

        <!-- Category Pills -->
        @php
            $cats = $items->pluck('category')->unique()->filter()->values();
        @endphp
        @if($cats->count() > 1)
        <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-slate-100">
            <button type="button" onclick="selectFilterCategory('')" class="cat-pill active-cat px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-900 text-white transition" data-cat="">
                الكل ({{ $items->count() }})
            </button>
            @foreach($cats as $cat)
                @php
                    $count = $items->where('category', $cat)->count();
                @endphp
                <button type="button" onclick="selectFilterCategory('{{ $cat }}')" class="cat-pill px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-cat="{{ $cat }}">
                    {{ $cat }} ({{ $count }})
                </button>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Main Audit Form -->
    <form action="{{ route('warehouse.dailyChecks.store') }}" method="POST" id="dailyCheckForm" class="space-y-6">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        <!-- Header Info Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">تاريخ الجرد الفعلي *</label>
                    <input type="date" name="check_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">أمين المخزن / المشرف المسجل</label>
                    <input type="text" value="{{ Auth::user()->name }} (أمين المخزن المركزي)" readonly class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 outline-none cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات لجنة الجرد العام</label>
                    <input type="text" name="notes" placeholder="تم الجرد بحضور مسؤول المخازن ومراقبة الجودة..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Inventory Items Rich Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <span class="text-xs font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-amber-600"></i>
                    <span>جدول بنود الجرد والمطابقة الحية ({{ $items->count() }} صنف)</span>
                </span>
                <span class="text-[11px] text-slate-400">الكمية الفعلية يتم احتساب الفارق والأثر المالي لها لحظياً</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs" id="auditItemsTable">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-black border-b border-slate-200">
                            <th class="py-3 px-3 text-center w-12">#</th>
                            <th class="py-3 px-3 w-16">صورة الصنف</th>
                            <th class="py-3 px-3">اسم الصنف والتوصيف الفني</th>
                            <th class="py-3 px-3 text-center">التصنيف</th>
                            <th class="py-3 px-3 text-center font-mono">الرصيد الدفتري</th>
                            <th class="py-3 px-3 text-center">الكمية الفعلية بالمخزن *</th>
                            <th class="py-3 px-3 text-center">فرق الكمية</th>
                            <th class="py-3 px-3 text-left font-mono">سعر التكلفة</th>
                            <th class="py-3 px-3 text-left font-mono">الأثر المالي للفرق</th>
                            <th class="py-3 px-3">سبب الفارق إن وجد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $idx => $it)
                            @php
                                $cost = $category === 'finished_goods' ? (float)$it->cost_price : (float)$it->unit_cost;
                                $itemType = $category === 'finished_goods' ? 'product' : 'raw_material';
                                $imgUrl = $it->image_url;
                                $stock = (float)$it->current_stock;
                            @endphp
                            <tr class="audit-row hover:bg-amber-50/30 transition" 
                                id="row-{{ $idx }}"
                                data-index="{{ $idx }}"
                                data-name="{{ strtolower($it->name) }}"
                                data-code="{{ strtolower($it->code) }}"
                                data-cat="{{ $it->category }}"
                                data-system-qty="{{ $stock }}"
                                data-cost="{{ $cost }}">
                                
                                <td class="py-3 px-3 text-center font-mono text-slate-400 text-[11px]">{{ $idx + 1 }}</td>
                                
                                <!-- Image with Zoom -->
                                <td class="py-3 px-3">
                                    <img src="{{ $imgUrl }}" 
                                         alt="{{ $it->name }}" 
                                         class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:scale-105 transition bg-white flex-shrink-0"
                                         onclick="openAuditZoomModal('{{ $imgUrl }}', '{{ addslashes($it->name) }}', '{{ $it->code }}')">
                                </td>

                                <!-- Name & Code -->
                                <td class="py-3 px-3">
                                    <div class="font-black text-slate-900 text-sm leading-snug">{{ $it->name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-[10px] text-slate-500 font-bold bg-slate-100 px-1.5 py-0.5 rounded">{{ $it->code }}</span>
                                        @if($it->package_info)
                                            <span class="text-[10px] text-blue-700 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-boxes-packing text-[9px]"></i>
                                                {{ $it->package_info }}
                                            </span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $it->id }}">
                                    <input type="hidden" name="items[{{ $idx }}][item_type]" value="{{ $itemType }}">
                                    <input type="hidden" name="items[{{ $idx }}][system_qty]" value="{{ $stock }}">
                                    <input type="hidden" name="items[{{ $idx }}][unit_cost]" value="{{ $cost }}">
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $it->category }}
                                    </span>
                                </td>

                                <!-- System Quantity -->
                                <td class="py-3 px-3 text-center font-mono font-black text-slate-800 text-sm">
                                    {{ number_format($stock, 2) }}
                                    <span class="text-[10px] font-normal text-slate-400 block">{{ $it->unit }}</span>
                                </td>

                                <!-- Actual Quantity Input -->
                                <td class="py-3 px-3 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <input type="number" 
                                               step="0.01" 
                                               name="items[{{ $idx }}][actual_qty]" 
                                               id="actual-{{ $idx }}" 
                                               value="{{ $stock }}" 
                                               required 
                                               oninput="onActualQtyChange({{ $idx }})"
                                               class="w-28 px-3 py-1.5 bg-amber-50/50 border border-amber-300 rounded-xl text-xs font-black text-slate-900 text-center font-mono focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none shadow-xs">
                                        <button type="button" onclick="setRowActualToSystem({{ $idx }})" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg transition" title="مطابقة هذا الصنف بالدفتري">
                                            <i class="fa-solid fa-equals text-xs"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Discrepancy (Live) -->
                                <td class="py-3 px-3 text-center font-mono font-black text-xs" id="diff-badge-{{ $idx }}">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-600">
                                        مطابق (0)
                                    </span>
                                </td>

                                <!-- Unit Cost -->
                                <td class="py-3 px-3 text-left font-mono font-bold text-slate-600">
                                    {{ number_format($cost, 2) }} ج.م
                                </td>

                                <!-- Financial Impact (Live) -->
                                <td class="py-3 px-3 text-left font-mono font-black text-xs" id="impact-badge-{{ $idx }}">
                                    <span class="text-slate-400">0.00 ج.م</span>
                                </td>

                                <!-- Reason input -->
                                <td class="py-3 px-3">
                                    <input type="text" 
                                           name="items[{{ $idx }}][reason]" 
                                           id="reason-{{ $idx }}" 
                                           placeholder="مطابق / سبب الفرق..." 
                                           class="w-full px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-amber-500 outline-none">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty Filter State -->
            <div id="noResultsState" class="hidden p-12 text-center text-slate-400 text-xs">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-2 text-slate-300 block"></i>
                <p class="font-bold text-slate-600">لا توجد أصناف مطابقة للبحث أو الفلتر المحدد.</p>
                <button type="button" onclick="clearSearchFilter()" class="mt-2 text-amber-600 font-bold underline">إعادة ضبط البحث</button>
            </div>
        </div>

        <!-- Submit Actions -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-xs">
                    <b class="text-slate-900 block">اعتماد فوري وترحيل التسويات المخزنية</b>
                    <p class="text-slate-500">سيتم حفظ محضر الجرد وتعديل رصيد المخزن الفعلي آلياً ليتطابق مع ما تم إدخاله</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('warehouse.dailyChecks') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                    إلغاء والعودة
                </a>
                <button type="submit" class="px-8 py-3 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-check-double text-amber-400"></i>
                    <span>اعتماد الجرد وتسوية الأرصدة بالمخزن</span>
                </button>
            </div>
        </div>
    </form>

</div>

<!-- Lightbox Modal for Image Zoom -->
<div id="auditZoomModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeAuditZoomModal()">
    <div class="bg-white rounded-3xl max-w-lg w-full p-4 space-y-3 overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h4 id="zoomModalTitle" class="font-black text-slate-900 text-sm"></h4>
                <span id="zoomModalCode" class="text-[11px] font-mono font-bold text-slate-400"></span>
            </div>
            <button type="button" onclick="closeAuditZoomModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="rounded-2xl overflow-hidden bg-slate-100 flex items-center justify-center max-h-[70vh]">
            <img id="zoomModalImg" src="" alt="Zoom" class="max-h-[65vh] w-auto object-contain">
        </div>
    </div>
</div>

<script>
    let activeCategory = '';

    function onActualQtyChange(idx) {
        const row = document.getElementById('row-' + idx);
        if (!row) return;

        const systemQty = parseFloat(row.getAttribute('data-system-qty')) || 0;
        const unitCost = parseFloat(row.getAttribute('data-cost')) || 0;
        const actualInput = document.getElementById('actual-' + idx);
        const actualQty = parseFloat(actualInput ? actualInput.value : 0) || 0;

        const diff = actualQty - systemQty;
        const impact = diff * unitCost;

        const diffBadge = document.getElementById('diff-badge-' + idx);
        const impactBadge = document.getElementById('impact-badge-' + idx);
        const reasonInput = document.getElementById('reason-' + idx);

        if (Math.abs(diff) < 0.001) {
            diffBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-600">مطابق (0)</span>';
            impactBadge.innerHTML = '<span class="text-slate-400">0.00 ج.م</span>';
            if (reasonInput && !reasonInput.value) reasonInput.value = 'مطابق';
        } else if (diff > 0) {
            diffBadge.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[11px] bg-emerald-100 text-emerald-800 font-black">+${diff.toLocaleString('en-US')} (فائض)</span>`;
            impactBadge.innerHTML = `<span class="text-emerald-700 font-black">+${impact.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج.م</span>`;
            if (reasonInput && reasonInput.value === 'مطابق') reasonInput.value = 'فائض تشغيل';
        } else {
            diffBadge.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[11px] bg-rose-100 text-rose-800 font-black">${diff.toLocaleString('en-US')} (عجز)</span>`;
            impactBadge.innerHTML = `<span class="text-rose-700 font-black">${impact.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج.م</span>`;
            if (reasonInput && reasonInput.value === 'مطابق') reasonInput.value = 'هادر أو تلفيات';
        }

        recalculateLiveKPIs();
    }

    function setRowActualToSystem(idx) {
        const row = document.getElementById('row-' + idx);
        if (!row) return;
        const systemQty = parseFloat(row.getAttribute('data-system-qty')) || 0;
        const actualInput = document.getElementById('actual-' + idx);
        if (actualInput) {
            actualInput.value = systemQty;
            onActualQtyChange(idx);
        }
    }

    function autoMatchAll() {
        if (!confirm('هل تريد مطابقة كافة بنود الجرد لتساوي الرصيد الدفتري؟')) return;
        const rows = document.querySelectorAll('.audit-row');
        rows.forEach(r => {
            const idx = r.getAttribute('data-index');
            const systemQty = parseFloat(r.getAttribute('data-system-qty')) || 0;
            const actualInput = document.getElementById('actual-' + idx);
            if (actualInput) actualInput.value = systemQty;
            onActualQtyChange(idx);
        });
    }

    function resetAllActuals() {
        if (!confirm('هل تريد تصفير جميع الكميات الفعلية للبدء في الإدخال اليدوي؟')) return;
        const rows = document.querySelectorAll('.audit-row');
        rows.forEach(r => {
            const idx = r.getAttribute('data-index');
            const actualInput = document.getElementById('actual-' + idx);
            if (actualInput) actualInput.value = 0;
            onActualQtyChange(idx);
        });
    }

    function recalculateLiveKPIs() {
        const rows = document.querySelectorAll('.audit-row');
        let totalItems = rows.length;
        let matchedCount = 0;
        let surplusCount = 0;
        let deficitCount = 0;
        let surplusValue = 0;
        let deficitValue = 0;

        rows.forEach(r => {
            const idx = r.getAttribute('data-index');
            const systemQty = parseFloat(r.getAttribute('data-system-qty')) || 0;
            const unitCost = parseFloat(r.getAttribute('data-cost')) || 0;
            const actualInput = document.getElementById('actual-' + idx);
            const actualQty = parseFloat(actualInput ? actualInput.value : 0) || 0;

            const diff = actualQty - systemQty;
            const impact = diff * unitCost;

            if (Math.abs(diff) < 0.001) {
                matchedCount++;
            } else if (diff > 0) {
                surplusCount++;
                surplusValue += impact;
            } else {
                deficitCount++;
                deficitValue += Math.abs(impact);
            }
        });

        const netImpact = surplusValue - deficitValue;

        document.getElementById('kpi_matched_items').textContent = matchedCount.toLocaleString('en-US');
        document.getElementById('kpi_surplus_value').textContent = '+' + surplusValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
        document.getElementById('kpi_surplus_count').textContent = surplusCount + ' أصناف';
        
        document.getElementById('kpi_deficit_value').textContent = '-' + deficitValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
        document.getElementById('kpi_deficit_count').textContent = deficitCount + ' أصناف';

        const netEl = document.getElementById('kpi_net_impact');
        const badgeEl = document.getElementById('kpi_net_badge');
        if (Math.abs(netImpact) < 0.01) {
            netEl.textContent = '0.00 ج.م';
            netEl.className = 'text-xl font-black text-slate-900 font-mono';
            badgeEl.textContent = 'مطابق تام';
            badgeEl.className = 'text-[10px] font-bold text-slate-500 block';
        } else if (netImpact > 0) {
            netEl.textContent = '+' + netImpact.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
            netEl.className = 'text-xl font-black text-emerald-600 font-mono';
            badgeEl.textContent = 'صافي فائض بالمخزن';
            badgeEl.className = 'text-[10px] font-bold text-emerald-600 block';
        } else {
            netEl.textContent = netImpact.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
            netEl.className = 'text-xl font-black text-rose-600 font-mono';
            badgeEl.textContent = 'صافي عجز بالمخزن';
            badgeEl.className = 'text-[10px] font-bold text-rose-600 block';
        }
    }

    function filterInventoryRows() {
        const query = (document.getElementById('inventorySearchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.audit-row');
        let visibleCount = 0;

        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            const code = r.getAttribute('data-code') || '';
            const cat = r.getAttribute('data-cat') || '';

            const matchesQuery = !query || name.includes(query) || code.includes(query);
            const matchesCat = !activeCategory || cat === activeCategory;

            if (matchesQuery && matchesCat) {
                r.classList.remove('hidden');
                visibleCount++;
            } else {
                r.classList.add('hidden');
            }
        });

        const countEl = document.getElementById('visibleRowsCount');
        if (countEl) countEl.textContent = visibleCount + ' صنف';

        const noRes = document.getElementById('noResultsState');
        const table = document.getElementById('auditItemsTable');
        if (visibleCount === 0) {
            if (noRes) noRes.classList.remove('hidden');
            if (table) table.classList.add('hidden');
        } else {
            if (noRes) noRes.classList.add('hidden');
            if (table) table.classList.remove('hidden');
        }
    }

    function selectFilterCategory(cat) {
        activeCategory = cat;
        document.querySelectorAll('.cat-pill').forEach(btn => {
            if (btn.getAttribute('data-cat') === cat) {
                btn.className = 'cat-pill active-cat px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-900 text-white transition';
            } else {
                btn.className = 'cat-pill px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
            }
        });
        filterInventoryRows();
    }

    function clearSearchFilter() {
        document.getElementById('inventorySearchInput').value = '';
        selectFilterCategory('');
    }

    function openAuditZoomModal(url, title, code) {
        document.getElementById('zoomModalImg').src = url;
        document.getElementById('zoomModalTitle').textContent = title;
        document.getElementById('zoomModalCode').textContent = code;
        document.getElementById('auditZoomModal').classList.remove('hidden');
    }

    function closeAuditZoomModal() {
        document.getElementById('auditZoomModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        recalculateLiveKPIs();
    });
</script>
@endsection
