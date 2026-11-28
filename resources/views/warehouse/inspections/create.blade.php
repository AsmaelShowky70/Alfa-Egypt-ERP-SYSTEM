@extends('layouts.app')

@section('title', 'تحرير محضر فحص واستلام جديد')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                <span>تحرير محضر فحص واستلام بضاعة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">توثيق الفحص الظاهري والكمي للخامات أو المنتج التام أو قطع الغيار، وفصل المقبول عن المرفوض مع الترحيل التلقائي للمخزن</p>
        </div>
        <a href="{{ route('warehouse.inspections.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لقائمة المحاضر
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('warehouse.inspections.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Header Metadata -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">تاريخ الفحص والاستلام *</label>
                    <input type="date" name="inspection_date" value="{{ old('inspection_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">مصدر التوريد *</label>
                    <select name="source_type" id="sourceTypeSelect" onchange="toggleSourceFields()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                        <option value="supplier">مورد خارجي</option>
                        <option value="internal_factory">مصنع داخلي (+14%)</option>
                        <option value="other">جهة أخرى / توريد مباشر</option>
                    </select>
                </div>

                <div id="supplierField">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">المورد</label>
                    <select name="supplier_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                        <option value="">-- اختر المورد --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="factoryField" class="hidden">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">المصنع الداخلي</label>
                    <select name="factory_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                        <option value="">-- اختر المصنع --</option>
                        @foreach($factories as $fac)
                            <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ربط بأمر شراء (اختياري)</label>
                    <select name="purchase_order_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                        <option value="">-- بدون ربط بأمر شراء --</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} ({{ $po->supplier?->name ?? $po->fromFactory?->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم فاتورة / بوليصة المورد</label>
                    <input type="text" name="supplier_invoice_number" value="{{ old('supplier_invoice_number') }}" placeholder="مثال: INV-4521" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم المندوب / السائق المسلّم</label>
                    <input type="text" name="delivered_by" value="{{ old('delivered_by') }}" placeholder="اسم المندوب أو السائق..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">القائم بالفحص (أمين المخزن) *</label>
                    <input type="text" name="inspector_name" value="{{ old('inspector_name', Auth::user()->name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">عضو لجنة الفحص / الجودة (اختياري)</label>
                    <input type="text" name="quality_member_name" value="{{ old('quality_member_name') }}" placeholder="مثال: م. جرجس أنور / م. فادي أشرف" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">
                </div>
            </div>

            <!-- Inspection Items Table -->
            <div class="border border-slate-200 rounded-2xl p-4 sm:p-5 bg-slate-50/60 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-800">بنود الفحص والاستلام:</h3>
                    <button type="button" onclick="addInspectionRow()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ إضافة صنف للفحص</span>
                    </button>
                </div>

                <div id="inspectionItemsContainer" class="space-y-3">
                    <!-- Dynamic rows -->
                </div>
            </div>

            <!-- Options & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات عامة للجنة الفحص</label>
                    <textarea name="notes" rows="2" placeholder="أي ملاحظات على سلامة التغليف أو الأوزان أو مطابقة المواصفات..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start gap-3">
                    <input type="checkbox" name="add_to_stock" id="addToStockCheck" value="1" checked class="mt-1 rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="addToStockCheck" class="text-xs cursor-pointer">
                        <span class="font-black text-emerald-900 block">إضافة الكميات المقبولة تلقائياً إلى رصيد المخزن المركزي</span>
                        <span class="text-[11px] text-emerald-700">سيتم إصدار إذن إضافة مخزنية رسمي تلقائياً بالكميات المقبولة فقط، واستبعاد المرفوض.</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('warehouse.inspections.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>اعتماد محضر الفحص والاستلام</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
const rawMaterialsList = @json($rawMaterialsJson);
const productsList = @json($productsJson);

let rowIndex = 0;

function toggleSourceFields() {
    const val = document.getElementById('sourceTypeSelect').value;
    document.getElementById('supplierField').classList.toggle('hidden', val !== 'supplier');
    document.getElementById('factoryField').classList.toggle('hidden', val !== 'internal_factory');
}

function buildItemOptions(type) {
    const list = type === 'product' ? productsList : rawMaterialsList;
    return list.map(i => `<option value="${i.id}">${i.name} (${i.unit})</option>`).join('');
}

function addInspectionRow() {
    const idx = rowIndex++;
    const div = document.createElement('div');
    div.className = 'grid grid-cols-1 sm:grid-cols-12 gap-2.5 bg-white p-3.5 rounded-2xl border border-slate-200 items-end';
    div.id = 'insp_row_' + idx;
    div.innerHTML = `
        <div class="sm:col-span-2">
            <label class="block text-[10px] font-bold text-slate-500 mb-1">النوع</label>
            <select name="items[${idx}][item_type]" onchange="onTypeChange(${idx}, this.value)" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
                <option value="raw_material">مادة خام / قطعة غيار</option>
                <option value="product">منتج تام (كوڤير)</option>
            </select>
        </div>
        <div class="sm:col-span-4">
            <label class="block text-[10px] font-bold text-slate-500 mb-1">الصنف *</label>
            <select name="items[${idx}][item_id]" id="insp_item_${idx}" required class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
                ${buildItemOptions('raw_material')}
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[10px] font-bold text-slate-500 mb-1">الكمية الموردة *</label>
            <input type="number" step="0.01" min="0.01" name="items[${idx}][delivered_quantity]" id="deliv_${idx}" value="100" oninput="syncQty(${idx}, true)" required class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[10px] font-bold text-emerald-700 mb-1">الكمية المقبولة *</label>
            <input type="number" step="0.01" min="0" name="items[${idx}][accepted_quantity]" id="acc_${idx}" value="100" oninput="syncQty(${idx}, false)" required class="w-full px-2.5 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-black text-emerald-800">
        </div>
        <div class="sm:col-span-1 text-center">
            <label class="block text-[10px] font-bold text-rose-600 mb-1">مرفوض</label>
            <div id="rej_${idx}" class="py-2 text-xs font-black text-rose-600">0</div>
        </div>
        <div class="sm:col-span-1 text-left">
            <button type="button" onclick="removeRow(${idx})" class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold" title="حذف">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <div class="sm:col-span-12">
            <input type="text" name="items[${idx}][notes]" placeholder="ملاحظات فحص هذا البند (مثال: 5 قطع تالفة بالتغليف، الوزن مطابق...)" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px]">
        </div>
    `;
    document.getElementById('inspectionItemsContainer').appendChild(div);
}

function onTypeChange(idx, val) {
    document.getElementById('insp_item_' + idx).innerHTML = buildItemOptions(val);
}

function syncQty(idx, updateAcc) {
    const d = parseFloat(document.getElementById('deliv_' + idx).value) || 0;
    const accEl = document.getElementById('acc_' + idx);
    if (updateAcc) {
        accEl.value = d;
    }
    const a = Math.min(d, parseFloat(accEl.value) || 0);
    document.getElementById('rej_' + idx).textContent = Math.max(0, (d - a)).toFixed(2).replace(/\.00$/, '');
}

function removeRow(idx) {
    const container = document.getElementById('inspectionItemsContainer');
    if (container.children.length <= 1) return;
    document.getElementById('insp_row_' + idx).remove();
}

document.addEventListener('DOMContentLoaded', () => {
    addInspectionRow();
});
</script>
@endsection
