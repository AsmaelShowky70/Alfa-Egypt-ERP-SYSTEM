@extends('layouts.app')

@section('title', 'قطع غيار الماكينات والسيور')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-wrench text-blue-600"></i>
                <span>مخزن قطع الغيار ومستلزمات صيانة الماكينات</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">سيور ناقلة، سخانات لحام، سكاكين تقطيع كوفير، فوتوسيل وحساسات الماكينات بالصور والكميات</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddSpareModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-bold transition flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة قطعة غيار جديدة</span>
            </button>
            <a href="{{ route('warehouse.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>رجوع للمخزن</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 block">إجمالي بنود قطع الغيار</span>
                <span class="text-2xl font-black text-slate-900">{{ $spareParts->total() }}</span>
                <span class="text-[10px] text-slate-500 font-bold mr-1">بند مسجل</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-gears"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 block">إجمالي القيمة المخزنية</span>
                <span class="text-2xl font-black text-emerald-700 font-mono">{{ number_format($spareParts->sum(fn($i) => $i->current_stock * $i->unit_cost), 2) }}</span>
                <span class="text-[10px] text-slate-500 font-bold mr-1">ج.م</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 block">القطع عند أو تحت حد الأمان</span>
                <span class="text-2xl font-black text-rose-600">{{ $spareParts->getCollection()->filter(fn($sp) => $sp->isLowStock())->count() }}</span>
                <span class="text-[10px] text-rose-500 font-bold mr-1">تحتاج طلب شراء</span>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- Spare Parts Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                        <th class="py-3 px-3 w-16 text-center">الصورة</th>
                        <th class="py-3 px-4">كود الصنف</th>
                        <th class="py-3 px-4">اسم قطعة الغيار</th>
                        <th class="py-3 px-4 text-center">الوحدة</th>
                        <th class="py-3 px-4 font-mono text-left">التكلفة للوحدة</th>
                        <th class="py-3 px-4 text-center">الرصيد المتاح</th>
                        <th class="py-3 px-4 text-center">حد الأمان</th>
                        <th class="py-3 px-4">الموقع بالمستودع</th>
                        <th class="py-3 px-4">المورد المعتمد</th>
                        <th class="py-3 px-4 text-center w-28">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($spareParts as $sp)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Image with Zoom -->
                            <td class="py-2.5 px-3 text-center">
                                <img src="{{ $sp->image_url }}" 
                                     alt="{{ $sp->name }}" 
                                     class="w-10 h-10 object-cover rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:scale-105 transition bg-white inline-block"
                                     onclick="openZoomSpareModal('{{ $sp->image_url }}', '{{ addslashes($sp->name) }}')">
                            </td>

                            <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                <span class="bg-slate-100 px-2 py-0.5 rounded-md">{{ $sp->code }}</span>
                            </td>

                            <td class="py-3 px-4 font-black text-slate-900 text-sm">
                                <div>{{ $sp->name }}</div>
                                @if($sp->package_info)
                                    <span class="text-[10px] text-slate-400 font-normal block">{{ $sp->package_info }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center text-slate-600">{{ $sp->unit }}</td>

                            <td class="py-3 px-4 font-bold text-slate-800 text-left font-mono">
                                {{ number_format($sp->unit_cost, 2) }} ج.م
                            </td>

                            <td class="py-3 px-4 text-center font-extrabold {{ $sp->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($sp->current_stock) }} {{ $sp->unit }}
                                @if($sp->isLowStock())
                                    <span class="inline-block text-[10px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded mr-1">نواقص</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center text-slate-500 font-mono">{{ number_format($sp->reorder_level) }} {{ $sp->unit }}</td>

                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $sp->warehouse_location ?? 'دولاب قطع الغيار' }}</td>

                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $sp->supplier->name ?? 'مورد عام' }}</td>

                            <!-- Action Buttons -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick="openEditSpareModal(this)"
                                            data-id="{{ $sp->id }}"
                                            data-name="{{ $sp->name }}"
                                            data-unit="{{ $sp->unit }}"
                                            data-stock="{{ $sp->current_stock }}"
                                            data-reorder="{{ $sp->reorder_level }}"
                                            data-cost="{{ $sp->unit_cost }}"
                                            data-location="{{ $sp->warehouse_location }}"
                                            data-supplier="{{ $sp->supplier_id }}"
                                            title="تعديل بيانات قطعة الغيار"
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="openAdjustSpareModal(this)"
                                            data-id="{{ $sp->id }}"
                                            data-name="{{ $sp->name }}"
                                            data-unit="{{ $sp->unit }}"
                                            data-stock="{{ (float)$sp->current_stock }}"
                                            title="تسوية رصيد جردي"
                                            class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer">
                                        <i class="fa-solid fa-scale-balanced"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-10 text-slate-400">لا توجد قطع غيار مسجلة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($spareParts->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $spareParts->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: ADD SPARE PART -->
    <div id="addSpareModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-blue-600"></i>
                    <span>إضافة قطعة غيار جديدة لمخزن الماكينات</span>
                </h3>
                <button type="button" onclick="closeModal('addSpareModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.spareParts.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم قطعة الغيار *</label>
                        <input type="text" name="name" required placeholder="مثال: سير ناقل حراري 250 مم أو حساس فوتوسيل" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <input type="text" name="unit" required placeholder="مثال: قطعة، متر، طقم" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الحالي بالمخزن *</label>
                        <input type="number" step="1" min="0" name="current_stock" required value="1" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر التكلفة للوحدة (ج.م) *</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" required placeholder="0.00" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان (إعادة الطلب) *</label>
                        <input type="number" step="1" min="0" name="reorder_level" required value="2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">مكان التخزين بالمستودع</label>
                        <input type="text" name="warehouse_location" placeholder="مثال: دولاب قطع الغيار - رف 2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المورد المعتمد</label>
                        <select name="supplier_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- بدون مورد محدد --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">صورة قطعة الغيار (اختياري)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('addSpareModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-save"></i>
                        <span>حفظ وإضافة قطعة الغيار</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT SPARE PART -->
    <div id="editSpareModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل قطعة الغيار: <span id="editSpareTitle" class="text-blue-700"></span></span>
                </h3>
                <button type="button" onclick="closeModal('editSpareModal')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <form id="editSpareForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">اسم قطعة الغيار *</label>
                        <input type="text" id="edit_sp_name" name="name" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الوحدة *</label>
                        <input type="text" id="edit_sp_unit" name="unit" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد المتاح بالمخزن *</label>
                        <input type="number" step="1" min="0" id="edit_sp_stock" name="current_stock" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سعر التكلفة للوحدة (ج.م) *</label>
                        <input type="number" step="0.01" min="0" id="edit_sp_cost" name="unit_cost" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حد الأمان *</label>
                        <input type="number" step="1" min="0" id="edit_sp_reorder" name="reorder_level" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">مكان التخزين بالمستودع</label>
                        <input type="text" id="edit_sp_location" name="warehouse_location" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المورد المعتمد</label>
                        <select id="edit_sp_supplier" name="supplier_id" class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- بدون مورد محدد --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">تحديث صورة قطعة الغيار (اختياري)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs rounded-xl border-slate-200 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('editSpareModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fa-solid fa-check"></i>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: ADJUST STOCK -->
    <div id="adjustSpareStockModal" onclick="if(event.target === this) closeModal('adjustSpareStockModal')" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden" style="display: none;">
        <div onclick="event.stopPropagation()" class="modal-dialog bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-emerald-600"></i>
                    <span>تسوية رصيد جردي لقطعة الغيار</span>
                </h3>
                <button type="button" onclick="closeModal('adjustSpareStockModal'); event.stopPropagation();" class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold transition cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('warehouse.adjustStock') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="item_type" value="raw_material">
                <input type="hidden" id="adjust_sp_id" name="item_id" value="">

                <div class="bg-blue-50/60 p-3 rounded-2xl border border-blue-100 space-y-1">
                    <div id="adjust_sp_name" class="font-bold text-blue-900 text-sm"></div>
                    <div class="text-blue-700">
                        الرصيد الدفتري الحالي: 
                        <span id="adjust_sp_stock" class="font-black text-sm"></span> 
                        <span id="adjust_sp_unit"></span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرصيد الفعلي الجديد بعد الجرد *</label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="1" min="0" id="adjust_sp_new_stock" name="new_stock" required class="flex-1 text-sm font-black rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <span id="adjust_sp_unit_label" class="font-bold text-slate-600"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">سبب التسوية الجردية *</label>
                        <select name="reason" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="جرد دوري لمخزن قطع الغيار">جرد دوري لمخزن قطع الغيار</option>
                            <option value="استهلاك صيانة لماكينة">استهلاك صيانة لماكينة</option>
                            <option value="إضافة رصيد مرحل أو مشتريات">إضافة رصيد مرحل أو مشتريات</option>
                            <option value="تلف أو كسر أثناء التركيب">تلف أو كسر أثناء التركيب</option>
                            <option value="أخرى">أخرى (مذكورة بالملاحظات)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ملاحظات إضافية</label>
                        <textarea name="notes" rows="2" placeholder="اكتب رقم محضر الجرد أو تفاصيل التسوية..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('adjustSpareStockModal'); event.stopPropagation();" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md cursor-pointer">
                        <i class="fa-solid fa-check-double"></i>
                        <span>اعتماد تسوية الرصيد</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: ZOOM LIGHTBOX -->
    <div id="zoomSpareModal" class="modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4 hidden" style="display: none;">
        <div class="modal-dialog bg-white rounded-3xl max-w-xl w-full p-4 shadow-2xl space-y-3 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 id="zoomSpareTitle" class="font-black text-slate-900 text-sm"></h3>
                <button type="button" onclick="closeModal('zoomSpareModal')" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
            </div>
            <div class="bg-slate-50 p-2 rounded-2xl flex items-center justify-center">
                <img id="zoomSpareImg" src="" alt="" class="max-h-[70vh] rounded-xl object-contain shadow-sm">
            </div>
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
        if (!modalId) {
            document.querySelectorAll('.modal-wrapper').forEach(function(m) {
                m.style.display = 'none';
                m.classList.add('hidden');
            });
            return;
        }
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }

    function openAddSpareModal() {
        openModal('addSpareModal');
    }

    function openEditSpareModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const unit = btn.dataset.unit;
        const stock = btn.dataset.stock;
        const reorder = btn.dataset.reorder;
        const cost = btn.dataset.cost;
        const location = btn.dataset.location || '';
        const supplier = btn.dataset.supplier || '';

        document.getElementById('editSpareTitle').textContent = name;
        document.getElementById('editSpareForm').action = "{{ url('warehouse/spare-parts') }}/" + id;
        document.getElementById('edit_sp_name').value = name;
        document.getElementById('edit_sp_unit').value = unit;
        document.getElementById('edit_sp_stock').value = stock;
        document.getElementById('edit_sp_reorder').value = reorder;
        document.getElementById('edit_sp_cost').value = cost;
        document.getElementById('edit_sp_location').value = location;
        document.getElementById('edit_sp_supplier').value = supplier;

        openModal('editSpareModal');
    }

    function openAdjustSpareModal(btnOrId, name, unit, stock) {
        let idVal = btnOrId;
        let nameVal = name;
        let unitVal = unit;
        let stockVal = stock;
        if (typeof btnOrId === 'object' && btnOrId !== null && btnOrId.dataset) {
            idVal = btnOrId.dataset.id;
            nameVal = btnOrId.dataset.name;
            unitVal = btnOrId.dataset.unit;
            stockVal = btnOrId.dataset.stock;
        }
        document.getElementById('adjust_sp_id').value = idVal;
        document.getElementById('adjust_sp_name').textContent = nameVal || 'قطعة الغيار';
        document.getElementById('adjust_sp_stock').textContent = Number(stockVal || 0).toLocaleString();
        document.getElementById('adjust_sp_unit').textContent = unitVal || 'قطعة';
        document.getElementById('adjust_sp_unit_label').textContent = unitVal || 'قطعة';
        document.getElementById('adjust_sp_new_stock').value = stockVal || 0;

        openModal('adjustSpareStockModal');
    }

    function openZoomSpareModal(src, title) {
        document.getElementById('zoomSpareImg').src = src;
        document.getElementById('zoomSpareTitle').textContent = title;
        openModal('zoomSpareModal');
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
