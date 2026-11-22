@extends('layouts.app')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">إضافة عميل جديد لشركة ألفا إيجبت</h1>
            <p class="text-xs text-slate-500">تسجيل بيانات المطعم أو الشركة وشعارها لتخصيص تصاميم الكوڤير وأوامر الشغل</p>
        </div>
        <a href="{{ route('sales.customers') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع لقائمة العملاء
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('sales.customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اسم الشركة / العميل *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="مثال: كشري التحرير، مطعم أبو طارق" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">كود العميل الفريد *</label>
                    <input type="text" name="code" value="{{ old('code', 'CUST-' . str_pad(\App\Models\Customer::count() + 1, 3, '0', STR_PAD_LEFT)) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">اسم مسؤول المشتريات / التواصل</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="أ. فلان الفلاني" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">رقم الهاتف / الواتساب</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01xxxxxxxxx" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="orders@client.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">شروط وطريقة السداد</label>
                    <input type="text" name="payment_terms" value="{{ old('payment_terms', 'آجل 30 يوم') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">الرقم الضريبي (اختياري)</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">السجل التجاري (اختياري)</label>
                    <input type="text" name="commercial_register" value="{{ old('commercial_register') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">العنوان والفروع الرئيسية</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="القاهرة، فرع وسط البلد..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
            </div>

            <!-- Logo Upload & URL -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">رفع لوجو الشركة (ملف صورة)</label>
                    <input type="file" name="logo" accept="image/*" class="text-xs text-slate-600 file:mr-0 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">أو رابط لوجو من موقع الشركة (URL)</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://..." class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">ملاحظات وشروط خاصة بالتعاقد</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="مثال: يفضل استلام الدفعات أسبوعياً، ألوان اللوجو أزرق داكن...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('sales.customers') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs rounded-xl transition">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ بيانات العميل</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
