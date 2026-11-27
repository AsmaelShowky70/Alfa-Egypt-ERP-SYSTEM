@extends('layouts.app')

@section('title', 'طلبات واستلام خامات الإنتاج من المخزن المركزي')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                <span>طلبات واستلام خامات الإنتاج من المخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500">متابعة خروج مستلزمات الإنتاج من المخزن وتأكيد استلامها الفعلي على خطوط الإنتاج بالمصانع</p>
        </div>
        <button onclick="document.getElementById('newRequisitionModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إنشاء طلب صرف خامات جديد</span>
        </button>
    </div>

    <!-- Summary KPI Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي طلبات الصرف</p>
                <h3 class="text-xl font-black text-slate-900">{{ $requisitions->total() }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">بانتظار موافقة وصرف المخزن</p>
                <h3 class="text-xl font-black text-amber-700">{{ $pendingCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-sm flex items-center gap-4 bg-gradient-to-l from-blue-50/50 to-white">
            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl flex-shrink-0 shadow">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-blue-600">تم الصرف (بانتظار استلامك)</p>
                <h3 class="text-xl font-black text-blue-800">{{ $inTransitCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">تم استلامها بالمصنع</p>
                <h3 class="text-xl font-black text-emerald-700">{{ $receivedCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Requisitions List -->
    <div class="space-y-4">
        @forelse($requisitions as $req)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-black text-sm flex-shrink-0">
                            MR
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $req->req_number }}</h3>
                                <span class="bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $req->productionOrder?->order_number ?? 'طلب مباشر' }}
                                </span>
                                <span class="px-3 py-0.5 rounded-full text-xs font-black border {{ $req->status_badge['color'] }}">
                                    {{ $req->status_badge['label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                المنتج المستهدف: <b class="text-slate-800">{{ $req->productionOrder?->product?->name ?? 'غير محدد' }}</b>
                                @if($req->productionOrder?->salesOrder?->customer)
                                    | العميل: <b class="text-brand-700">{{ $req->productionOrder->salesOrder->customer->name }}</b>
                                @endif
                                | تاريخ الطلب: {{ $req->requested_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    <!-- Action: Confirm Receipt if Issued -->
                    <div class="flex items-center gap-3">
                        @if($req->status === 'issued')
                            <form action="{{ route('operations.requisitions.confirmReceipt', $req->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-600/30 hover:shadow-xl transition flex items-center gap-2">
                                    <i class="fa-solid fa-box-open"></i>
                                    <span>تأكيد استلام الخامات على خط الإنتاج بالمصنع</span>
                                </button>
                            </form>
                        @elseif($req->status === 'received')
                            <div class="text-left text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3.5 py-2 rounded-xl flex items-center gap-2">
                                <i class="fa-solid fa-check-double text-emerald-600"></i>
                                <span>تم الاستلام بواسطة: <b>{{ $req->receivedByUser?->name ?? 'مدير التشغيل' }}</b> في {{ $req->received_at?->format('Y-m-d H:i') }}</span>
                            </div>
                        @else
                            <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-clock"></i>
                                <span>الطلب قيد المراجعة والصرف من أمين المخزن</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Items Grid -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                                <th class="py-2.5 px-3">الخامة المطلوبة</th>
                                <th class="py-2.5 px-3">كود الخامة</th>
                                <th class="py-2.5 px-3">الكمية المطلوبة</th>
                                <th class="py-2.5 px-3">الكمية المنصرفة فعلياً</th>
                                <th class="py-2.5 px-3">موقف التوفر</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @foreach($req->items as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-sans font-bold text-slate-800">
                                        {{ $item->item_display_name }}
                                        @if($item->custom_material_name)
                                            <span class="inline-block bg-blue-50 text-blue-700 text-[10px] px-1.5 py-0.5 rounded font-bold mr-1">صنف يدوي</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                        {{ $item->rawMaterial?->code ?? 'RM-MANUAL' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-black text-slate-900">
                                        {{ number_format((float)$item->quantity_requested, 2) }} <span class="text-[10px] text-slate-500 font-sans font-bold">{{ $item->unit_display }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-black text-emerald-700">
                                        {{ number_format((float)$item->quantity_issued, 2) }} <span class="text-[10px] text-slate-500 font-sans font-bold">{{ $item->unit_display }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-sans">
                                        @if($item->quantity_missing > 0)
                                            <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px] font-bold">عجز {{ number_format((float)$item->quantity_missing, 2) }} {{ $item->unit_display }}</span>
                                        @else
                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-bold">متوفرة ومصروفة</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($req->notes)
                    <div class="text-xs bg-slate-50 text-slate-600 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-comment-dots text-slate-400"></i>
                        <span><b>ملاحظات الطلب:</b> {{ $req->notes }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center text-slate-400 text-xs border border-slate-200">
                لا توجد طلبات صرف خامات مسجلة حالياً.
            </div>
        @endforelse

        <div class="pt-4">
            {{ $requisitions->links() }}
        </div>
    </div>

    <!-- Modal to submit new material requisition -->
    <div id="newRequisitionModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full p-6 sm:p-8 max-h-[92vh] overflow-y-auto space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-dolly text-blue-600"></i>
                        <span>إنشاء طلب صرف خامات من المخزن المركزي</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">يمكنك البدء باختيار صنف كوڤير وتحميل محتوياته وزيادتها، أو الربط بأمر تشغيل قائم</p>
                </div>
                <button type="button" onclick="document.getElementById('newRequisitionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Mode Selector Tabs -->
            <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl text-xs font-bold">
                <button type="button" id="tabCouvertMode" onclick="setRequisitionMode('couvert')" class="flex-1 py-2 px-3 rounded-xl bg-white text-blue-700 shadow-xs flex items-center justify-center gap-1.5 transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-blue-600"></i>
                    <span>البدء بكوڤير / صنف وزيادة محتوياته (موصى به)</span>
                </button>
                <button type="button" id="tabWorkOrderMode" onclick="setRequisitionMode('workorder')" class="flex-1 py-2 px-3 rounded-xl text-slate-600 hover:text-slate-800 flex items-center justify-center gap-1.5 transition">
                    <i class="fa-solid fa-link text-slate-500"></i>
                    <span>ربط بأمر تشغيل مفتوح</span>
                </button>
            </div>

            <form action="{{ route('operations.requisitions.store') }}" method="POST" id="requisitionForm" class="space-y-4">
                @csrf

                <!-- Section A: Couvert & BOM Builder Box -->
                <div id="couvertSection" class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/90 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-blue-900 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-packing text-blue-600"></i>
                            <span>اختر الكوڤير / الصنف الأساسي لتحميل محتوياته</span>
                        </span>
                        <span class="text-[11px] text-blue-700 font-bold">شجرة المكونات المعيارية (BOM)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-7">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">الكوڤير المستهدف *</label>
                            <select name="product_id" id="couvertSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-1 focus:ring-blue-600 outline-none">
                                <option value="">-- حدد طقم الكوڤير المطلوب تجهيز خاماته --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">كمية الكوڤير المستهدفة (قطعة)</label>
                            <input type="number" name="couvert_quantity" id="couvertTargetQty" value="10000" min="1" step="100" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-blue-600 outline-none font-mono">
                        </div>

                        <div class="sm:col-span-2">
                            <button type="button" id="btnLoadBOM" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-1" title="تحميل كافة خامات ومكونات الكوڤير تلقائياً">
                                <i class="fa-solid fa-rotate"></i>
                                <span>تحميل BOM</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section B: Work Order Selector (Shown in work order mode) -->
                <div id="workOrderSection" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">اختر أمر التشغيل المرتبط *</label>
                    <select name="production_order_id" id="workOrderSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        <option value="">-- اختياري (تحديد أمر تشغيل قائم) --</option>
                        @foreach($activeProductionOrders as $wo)
                            <option value="{{ $wo->id }}">
                                {{ $wo->order_number }} — {{ $wo->product?->name }} ({{ $wo->factory?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Section C: Dynamic Raw Materials Items List -->
                <div class="space-y-3 border-t border-slate-100 pt-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-black text-slate-900">الخامات والمستلزمات المطلوبة من المخزن (يمكنك زيادة الكميات أو إضافة محتويات إضافية):</label>
                            <span class="text-[11px] text-slate-500">عدّل الكميات بحرية أو أضف مواد إضافية للكوڤير</span>
                        </div>
                        <button type="button" onclick="addMaterialRow()" class="text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>+ إضافة خامة إضافية للطلب</span>
                        </button>
                    </div>

                    <div id="itemsContainer" class="space-y-2.5">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Section D: Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات / تعليمات الصرف لإدارة المخازن</label>
                    <input type="text" name="notes" placeholder="مثال: صرف عاجل لتغطية زيادة وردية كوڤير أبو طارق مع مناديل إضافية..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-xs text-slate-500" id="totalMaterialsSummary">0 خامات مختارة</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('newRequisitionModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50 transition">إلغاء</button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>إرسال طلب الصرف للمخزن المركزي</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    const productsList = @json($productsJson);
    const rawMaterialsList = @json($rawMaterialsJson);

    let reqItemIndex = 0;

    function setRequisitionMode(mode) {
        const couvertSection = document.getElementById('couvertSection');
        const workOrderSection = document.getElementById('workOrderSection');
        const tabCouvert = document.getElementById('tabCouvertMode');
        const tabWO = document.getElementById('tabWorkOrderMode');

        if (mode === 'couvert') {
            couvertSection.classList.remove('hidden');
            workOrderSection.classList.add('hidden');
            tabCouvert.className = 'flex-1 py-2 px-3 rounded-xl bg-white text-blue-700 shadow-xs flex items-center justify-center gap-1.5 transition font-bold';
            tabWO.className = 'flex-1 py-2 px-3 rounded-xl text-slate-600 hover:text-slate-800 flex items-center justify-center gap-1.5 transition font-medium';
        } else {
            couvertSection.classList.add('hidden');
            workOrderSection.classList.remove('hidden');
            tabWO.className = 'flex-1 py-2 px-3 rounded-xl bg-white text-blue-700 shadow-xs flex items-center justify-center gap-1.5 transition font-bold';
            tabCouvert.className = 'flex-1 py-2 px-3 rounded-xl text-slate-600 hover:text-slate-800 flex items-center justify-center gap-1.5 transition font-medium';
        }
    }

    function buildMaterialOptions(selectedId) {
        let html = '<option value="">-- اختر خامة من المخزن المركزي --</option>';
        html += '<option value="custom" class="bg-blue-50 text-blue-700 font-bold">➕ كتابة صنف يدوي جديد (غير مسجل بالمخزن)</option>';
        rawMaterialsList.forEach(rm => {
            const sel = (selectedId && rm.id == selectedId) ? 'selected' : '';
            const pkg = rm.package_info ? ` [${rm.package_info}]` : '';
            html += `<option value="${rm.id}" ${sel}>${rm.name}${pkg} (المتاح: ${rm.stock.toLocaleString('en-US')} ${rm.unit})</option>`;
        });
        return html;
    }

    function addMaterialRow(rawMaterialId = '', quantity = '', unitLabel = 'قطعة', customName = '') {
        const container = document.getElementById('itemsContainer');
        const idx = reqItemIndex++;

        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 items-center bg-slate-50 hover:bg-slate-100/80 p-2.5 rounded-xl border border-slate-200/90 transition material-row';
        row.id = `mat-row-${idx}`;

        const isCustom = !rawMaterialId && customName;

        row.innerHTML = `
            <div class="col-span-6 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-500">الخامة / المستلزم</span>
                    <button type="button" class="text-[10px] text-blue-600 font-bold hover:underline" onclick="toggleRowMode(${idx})">
                        <span id="row-mode-label-${idx}">${isCustom ? 'اختر من المخزن 📋' : 'كتابة يدوي ✏️'}</span>
                    </button>
                </div>
                <div id="row-select-wrap-${idx}" class="${isCustom ? 'hidden' : ''}">
                    <select name="items[${idx}][raw_material_id]" class="mat-select w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-blue-600 outline-none">
                        ${buildMaterialOptions(rawMaterialId)}
                    </select>
                </div>
                <div id="row-custom-wrap-${idx}" class="${isCustom ? '' : 'hidden'}">
                    <input type="text" name="items[${idx}][custom_name]" id="row-custom-input-${idx}" value="${customName}" placeholder="اكتب اسم الخامة أو المستلزم يدوياً..." class="w-full px-2.5 py-1.5 bg-white border border-blue-300 rounded-lg text-xs font-bold text-blue-900 focus:ring-1 focus:ring-blue-600 outline-none">
                </div>
            </div>
            <div class="col-span-3 space-y-1">
                <span class="text-[10px] font-bold text-slate-500 block">الكمية المطلوبة</span>
                <input type="number" step="0.01" name="items[${idx}][quantity]" value="${quantity}" placeholder="الكمية" required class="mat-qty w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-black text-slate-900 focus:ring-1 focus:ring-blue-600 outline-none font-mono">
            </div>
            <div class="col-span-2 space-y-1">
                <span class="text-[10px] font-bold text-slate-500 block">وحدة الصرف</span>
                <select name="items[${idx}][unit]" class="mat-unit-select w-full px-1.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-blue-600 outline-none">
                    <option value="كجم" ${(unitLabel === 'كجم' || unitLabel.includes('كجم') || unitLabel.includes('كيلو')) ? 'selected' : ''}>كجم</option>
                    <option value="قطعة" ${(unitLabel === 'قطعة' || !unitLabel) ? 'selected' : ''}>قطعة</option>
                    <option value="رول" ${unitLabel === 'رول' ? 'selected' : ''}>رول</option>
                    <option value="متر" ${unitLabel === 'متر' ? 'selected' : ''}>متر</option>
                    <option value="كرتونة" ${unitLabel === 'كرتونة' ? 'selected' : ''}>كرتونة</option>
                    <option value="لتر" ${unitLabel === 'لتر' ? 'selected' : ''}>لتر</option>
                </select>
            </div>
            <div class="col-span-1 text-center pt-3">
                <button type="button" class="text-rose-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition" onclick="this.closest('.material-row').remove(); updateSummaryCount();" title="حذف الخامة">
                    <i class="fa-solid fa-trash-can text-sm"></i>
                </button>
            </div>
        `;

        container.appendChild(row);

        const select = row.querySelector('.mat-select');
        const unitSelect = row.querySelector('.mat-unit-select');

        select.addEventListener('change', function() {
            if (this.value === 'custom') {
                toggleRowMode(idx, true);
                return;
            }
            const rm = rawMaterialsList.find(m => m.id == this.value);
            if (rm && unitSelect) {
                if (rm.unit === 'كجم' || rm.unit.includes('كيلو') || rm.unit.includes('كجم')) {
                    unitSelect.value = 'كجم';
                } else if (rm.unit === 'قطعة') {
                    unitSelect.value = 'قطعة';
                } else {
                    unitSelect.value = rm.unit;
                }
            }
        });

        updateSummaryCount();
    }

    function toggleRowMode(idx, forceCustom = null) {
        const selectWrap = document.getElementById(`row-select-wrap-${idx}`);
        const customWrap = document.getElementById(`row-custom-wrap-${idx}`);
        const modeLabel = document.getElementById(`row-mode-label-${idx}`);
        const customInput = document.getElementById(`row-custom-input-${idx}`);
        const select = selectWrap.querySelector('.mat-select');

        const isCurrentlyCustom = !customWrap.classList.contains('hidden');
        const shouldBeCustom = (forceCustom !== null) ? forceCustom : !isCurrentlyCustom;

        if (shouldBeCustom) {
            selectWrap.classList.add('hidden');
            customWrap.classList.remove('hidden');
            modeLabel.textContent = 'اختر من المخزن 📋';
            if (select) select.value = '';
            if (customInput) customInput.focus();
        } else {
            customWrap.classList.add('hidden');
            selectWrap.classList.remove('hidden');
            modeLabel.textContent = 'كتابة يدوي ✏️';
            if (customInput) customInput.value = '';
        }
    }

    function updateSummaryCount() {
        const rows = document.querySelectorAll('.material-row');
        const summary = document.getElementById('totalMaterialsSummary');
        if (summary) {
            summary.textContent = `${rows.length} خامات مدرجة بالطلب`;
        }
    }

    // Load Couvert BOM into rows
    function loadCouvertBOM() {
        const couvertSelect = document.getElementById('couvertSelect');
        const targetQtyInput = document.getElementById('couvertTargetQty');
        const container = document.getElementById('itemsContainer');

        const prodId = couvertSelect.value;
        const targetQty = parseFloat(targetQtyInput.value) || 10000;

        const prod = productsList.find(p => p.id == prodId);
        if (!prod) return;

        container.innerHTML = '';
        reqItemIndex = 0;

        if (prod.boms && prod.boms.length > 0) {
            prod.boms.forEach(b => {
                const totalNeeded = b.qty_per_unit * targetQty;
                addMaterialRow(b.raw_material_id, totalNeeded, b.unit);
            });
        } else {
            // Default first material
            if (rawMaterialsList.length > 0) {
                addMaterialRow(rawMaterialsList[0].id, targetQty, rawMaterialsList[0].unit);
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const btnLoad = document.getElementById('btnLoadBOM');
        const couvertSelect = document.getElementById('couvertSelect');

        if (btnLoad) {
            btnLoad.addEventListener('click', loadCouvertBOM);
        }

        if (couvertSelect) {
            couvertSelect.addEventListener('change', loadCouvertBOM);
            // Load initial BOM
            loadCouvertBOM();
        }
    });
</script>
@endsection