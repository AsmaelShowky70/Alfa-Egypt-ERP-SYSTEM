@extends('layouts.app')

@section('title', 'إصدار سند مخزني جديد')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">إصدار سند مخزن رسمي (صرف / استلام / بيع للغير)</h1>
            <p class="text-xs text-slate-500">توثيق حركة الأصناف بدقة وتحديث أرصدة المخزن المركزي تلقائياً مع خيار الطباعة</p>
        </div>
        <a href="{{ route('warehouse.vouchers') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لقائمة السندات
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('warehouse.vouchers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">نوع الإذن المخزني *</label>
                    <select name="type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none transition">
                        <option value="receipt_supplier">إذن استلام خامات من مورد خارجي</option>
                        <option value="receipt_factory_internal">إذن استلام من خط إنتاج داخلي (+14%)</option>
                        <option value="issue_production">إذن صرف خامات للتشغيل والإنتاج</option>
                        <option value="delivery_customer">إذن تسليم منتج تام لعميل</option>
                        <option value="sell_to_third_party">إذن بيع للغير من المخزن المركزي</option>
                        <option value="spare_part_maintenance">إذن صرف قطع غيار للصيانة</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">تاريخ السند *</label>
                    <input type="date" name="voucher_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اسم المستلم أو المسلّم</label>
                    <input type="text" name="received_or_delivered_by" placeholder="مثال: فني التشغيل، سائق العميل..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none transition">
                </div>
            </div>

            <!-- Single Item Row Entry -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-slate-50/70 space-y-4">
                <span class="text-xs font-extrabold text-slate-800 block">تفاصيل الصنف والكمية بالسند:</span>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">نوع الصنف</label>
                        <select name="items[0][item_type]" id="itemTypeSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-amber-600 outline-none">
                            <option value="raw_material">مادة خام / قطعة غيار</option>
                            <option value="product">منتج تام (كوڤير/وايبس/جوانتيات)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-5">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">اختر الصنف</label>
                        <select name="items[0][item_id]" id="itemIdSelect" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-amber-600 outline-none">
                            @foreach($rawMaterials as $rm)
                                <option value="{{ $rm->id }}" data-cost="{{ $rm->unit_cost }}">
                                    {{ $rm->name }} [رصيد: {{ number_format($rm->current_stock) }} {{ $rm->unit }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">الكمية</label>
                        <input type="number" step="0.01" name="items[0][quantity]" value="1000" min="0.01" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">سعر التكلفة (ج.م)</label>
                        <input type="number" step="0.0001" name="items[0][unit_cost]" id="itemUnitCost" value="0.90" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-amber-800 focus:ring-1 focus:ring-amber-600 outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">ملاحظات وبيان السند</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-600 outline-none transition" placeholder="مثال: استلام خامات كوفير مطابقة للمواصفات..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('warehouse.vouchers') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ السند وأرشفته</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
