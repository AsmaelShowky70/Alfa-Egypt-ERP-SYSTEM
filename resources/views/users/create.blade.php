@extends('layouts.app')

@section('title', 'إضافة حساب إداري جديد - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">إضافة حساب مدير جديد</h1>
            <p class="text-sm text-slate-500 mt-1">إنشاء حساب جديد وتعيين مستوى الصلاحية والقسم والبيانات الشخصية</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-secondary flex items-center gap-2">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لإدارة المستخدمين</span>
        </a>
    </div>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            @csrf

            <div class="p-6 sm:p-8 space-y-6">
                @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم بالكامل <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="مثال: م. أحمد عبد الله" class="input-field" value="{{ old('name') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني (لتسجيل الدخول) <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="name@alphaegypt1.com" class="input-field font-mono" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة المرور الأولية <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="******" class="input-field">
                        <p class="text-xs text-slate-400 mt-1">يمكن للمستخدم تغييرها لاحقاً من ملفه الشخصي</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">مستوى الصلاحية (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="input-field">
                            <option value="owner">رئيس مجلس الإدارة (Owner) - وصول وتحكم كامل</option>
                            <option value="gm">المدير العام (GM) - إشراف وتشغيل استراتيجي كامل</option>
                            <option value="sales">مدير المبيعات (Sales) - طلبات، عملاء، وهندسة الكوڤير</option>
                            <option value="operations">مدير التشغيل (Operations) - خطوط الإنتاج والماكينات</option>
                            <option value="warehouse">مدير المخازن (Warehouse) - الخامات، المنتج التام، الجرد</option>
                            <option value="purchasing">مدير المشتريات (Purchasing) - التوريدات وأوامر الشراء</option>
                            <option value="finance">مدير الحسابات (Finance) - التكاليف، 14% مصانع، وإنذارات الخسارة</option>
                            <option value="maintenance">مدير الصيانة (Maintenance) - سرعات السيور والأعطال</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">المسمى الوظيفي <span class="text-rose-500">*</span></label>
                        <input type="text" name="job_title" required placeholder="مثال: مهندس تشغيل أول" class="input-field" value="{{ old('job_title') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">القسم / الإدارة <span class="text-rose-500">*</span></label>
                        <input type="text" name="department" required placeholder="مثال: إدارة الإنتاج والتعبئة" class="input-field" value="{{ old('department') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الهاتف والتواصل</label>
                        <input type="text" name="phone" placeholder="010XXXXXXXX" class="input-field font-mono" value="{{ old('phone') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">الصورة الشخصية (البروفايل)</label>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-slate-300 rounded-xl w-full text-sm text-slate-500">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn-secondary">إلغاء</a>
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <i class="fa-solid fa-user-check"></i>
                    <span>إنشاء الحساب الإداري</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
