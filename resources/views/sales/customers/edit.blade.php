@extends('layouts.app')

@section('title', 'تعديل بيانات العميل: ' . $customer->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">تعديل بيانات العميل: {{ $customer->name }}</h1>
            <p class="text-xs text-slate-500">تحديث أرقام التواصل أو العنوان أو اللوجو</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sales.customers.show', $customer->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع لملف العميل
            </a>
            <form action="{{ route('sales.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا العميل؟')" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl transition">
                    <i class="fa-solid fa-trash"></i> حذف
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('sales.customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اسم الشركة / العميل *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">كود العميل الفريد *</label>
                    <input type="text" name="code" value="{{ old('code', $customer->code) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اسم مسؤول التواصل</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">شروط السداد</label>
                    <input type="text" name="payment_terms" value="{{ old('payment_terms', $customer->payment_terms) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">حالة الحساب</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                        <option value="1" {{ $customer->status ? 'selected' : '' }}>نشط (يتعامل حالياً)</option>
                        <option value="0" {{ !$customer->status ? 'selected' : '' }}>متوقف مؤقتاً</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">الرقم الضريبي</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">العنوان</label>
                <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
            </div>

            <!-- Current Logo & Upload -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center gap-4">
                <img src="{{ $customer->logo_url }}" class="w-16 h-16 rounded-xl object-contain bg-white p-1 border border-slate-200">
                <div class="flex-1 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">تغيير لوجو الشركة</label>
                    <input type="file" name="logo" accept="image/*" class="text-xs text-slate-600 file:mr-0 file:ml-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 cursor-pointer">
                    <input type="url" name="logo_url" value="{{ old('logo_url', filter_var($customer->logo, FILTER_VALIDATE_URL) ? $customer->logo : '') }}" placeholder="أو رابط URL للوجو..." class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">ملاحظات</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('sales.customers.show', $customer->id) }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                    تحديث بيانات العميل
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
