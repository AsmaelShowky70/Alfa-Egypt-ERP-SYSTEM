@extends('layouts.app')

@section('title', 'إصدار أمر شراء جديد')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">إصدار أمر شراء جديد (مورد خارجي / خط داخلي +14%)</h1>
            <p class="text-xs text-slate-500">شراء الخامات لتغذية المخزن المركزي مع احتساب 14% تكلفة تشغيل عند الشراء من مصانع الشركة</p>
        </div>
        <a href="{{ route('purchasing.orders') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لأوامر الشراء
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('purchasing.orders.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($requisition)
                <input type="hidden" name="purchase_requisition_id" value="{{ $requisition->id }}">
                <div class="bg-purple-50 border border-purple-200 p-3 rounded-2xl text-xs text-purple-900 font-bold flex items-center gap-2">
                    <i class="fa-solid fa-link"></i>
                    <span>أمر الشراء مرتبط بطلب نواقص المخزن رقم: {{ $requisition->pr_number }}</span>
                </div>
            @endif

            <!-- Type of purchase: Internal Factory or External Supplier -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مصدر الشراء والتوريد *</label>
                    <select name="is_internal_factory" id="sourceTypeSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none">
                        <option value="0">مورد خارجي (بوليمرات / أوراق / قطع غيار)</option>
                        <option value="1">مصنع داخلي للشركة (+14% تكلفة إنتاج تشغيلية)</option>
                    </select>
                </div>

                <div id="supplierDiv">
                    <label class="block text-xs font-bold text-slate-700 mb-1">اختر المورد الخارجي</label>
                    <select name="supplier_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-purple-600 outline-none">
                        <option value="">-- حدد المورد --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->category }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="factoryDiv" class="hidden">
                    <label class="block text-xs font-bold text-slate-700 mb-1">اختر المصنع / الخط الداخلي المصدر (+14%)</label>
                    <select name="from_factory_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-indigo-700 focus:ring-1 focus:ring-purple-600 outline-none">
                        <option value="">-- حدد المصنع الداخلي --</option>
                        @foreach($factories as $fac)
                            <option value="{{ $fac->id }}">{{ $fac->name }} (هامش: {{ $fac->markup_percentage }}%)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ أمر الشراء *</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">الموعد المتوقع للتسليم *</label>
                    <input type="date" name="expected_delivery_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">المستودع الوجهة المستلم *</label>
                    <input type="text" name="destination_warehouse" value="المخزن المركزي - مجمع ألفا إيجبت" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-purple-600 outline-none">
                </div>
            </div>

            <!-- Single Item Row Entry -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-slate-50/70 space-y-4">
                <span class="text-xs font-extrabold text-slate-800 block">الصنف المطلوب شراؤه:</span>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">اسم الصنف / الخامة *</label>
                        <input type="text" name="items[0][item_name]" value="{{ $requisition && $requisition->items->first() ? $requisition->items->first()->rawMaterial->name : 'معلقة إنجليزي بلاستيك أبيض' }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none">
                        @if($requisition && $requisition->items->first())
                            <input type="hidden" name="items[0][raw_material_id]" value="{{ $requisition->items->first()->raw_material_id }}">
                        @endif
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">الكمية المطلوبة *</label>
                        <input type="number" step="0.01" name="items[0][quantity]" value="{{ $requisition && $requisition->items->first() ? $requisition->items->first()->needed_quantity : 50000 }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-purple-900 focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">سعر التكلفة للوحدة (ج.م) *</label>
                        <input type="number" step="0.0001" name="items[0][unit_price]" value="{{ $requisition && $requisition->items->first() ? $requisition->items->first()->estimated_unit_price : 0.90 }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-purple-900 focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 bg-white p-2.5 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-circle-info text-indigo-600"></i>
                    <span>في حالة الشراء الداخلي، سيتم تحميل <b>14% تكلفة تشغيل تلقائياً</b> لصالح المصنع الداخلي ويتم قيدها في الحسابات عند استلام المخزن.</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">شروط التوريد وملاحظات</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-purple-600 outline-none transition" placeholder="مثال: التوريد على دفعتين، مطابقة عينة الخامات للمواصفات..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('purchasing.orders') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-bold shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>إصدار أمر الشراء والتوريد</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sourceType = document.getElementById('sourceTypeSelect');
        const supplierDiv = document.getElementById('supplierDiv');
        const factoryDiv = document.getElementById('factoryDiv');

        function toggleSource() {
            if (sourceType.value === '1') {
                supplierDiv.classList.add('hidden');
                factoryDiv.classList.remove('hidden');
            } else {
                supplierDiv.classList.remove('hidden');
                factoryDiv.classList.add('hidden');
            }
        }

        sourceType.addEventListener('change', toggleSource);
        toggleSource();
    });
</script>
@endpush
