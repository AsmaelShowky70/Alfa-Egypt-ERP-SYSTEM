@extends('layouts.app')

@section('title', 'تعديل بيانات المستخدم: ' . $user->name . ' - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">تعديل بيانات الحساب: {{ $user->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">تعديل الصلاحيات والمسمى الوظيفي وحالة التفعيل أو تعيين كلمة مرور جديدة</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-secondary flex items-center gap-2">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لإدارة المستخدمين</span>
        </a>
    </div>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            @csrf
            @method('PUT')

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

                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-sm" alt="">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-2xl shadow-sm">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $user->job_title }} | {{ $user->department }}</p>
                        <span class="text-xs font-mono text-slate-400 mt-0.5 block">{{ $user->email }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم بالكامل <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required class="input-field" value="{{ old('name', $user->name) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required class="input-field font-mono" value="{{ old('email', $user->email) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">مستوى الصلاحية (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="input-field">
                            <option value="owner" {{ $user->role == 'owner' ? 'selected' : '' }}>رئيس مجلس الإدارة (Owner) - وصول كامل</option>
                            <option value="gm" {{ $user->role == 'gm' ? 'selected' : '' }}>المدير العام (GM) - إشراف وتشغيل استراتيجي</option>
                            <option value="sales" {{ $user->role == 'sales' ? 'selected' : '' }}>مدير المبيعات (Sales)</option>
                            <option value="operations" {{ $user->role == 'operations' ? 'selected' : '' }}>مدير التشغيل (Operations)</option>
                            <option value="warehouse" {{ $user->role == 'warehouse' ? 'selected' : '' }}>مدير المخازن (Warehouse)</option>
                            <option value="purchasing" {{ $user->role == 'purchasing' ? 'selected' : '' }}>مدير المشتريات (Purchasing)</option>
                            <option value="finance" {{ $user->role == 'finance' ? 'selected' : '' }}>مدير الحسابات (Finance)</option>
                            <option value="maintenance" {{ $user->role == 'maintenance' ? 'selected' : '' }}>مدير الصيانة (Maintenance)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">حالة الحساب <span class="text-rose-500">*</span></label>
                        <select name="status" required class="input-field">
                            <option value="1" {{ $user->status ? 'selected' : '' }}>حساب نشط ومفعل</option>
                            <option value="0" {{ !$user->status ? 'selected' : '' }}>حساب معطل وموقوف</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">المسمى الوظيفي <span class="text-rose-500">*</span></label>
                        <input type="text" name="job_title" required class="input-field" value="{{ old('job_title', $user->job_title) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">القسم / الإدارة <span class="text-rose-500">*</span></label>
                        <input type="text" name="department" required class="input-field" value="{{ old('department', $user->department) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone" class="input-field font-mono" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">تحديث كلمة المرور (اختياري)</label>
                        <input type="password" name="password" minlength="6" placeholder="اتركه فارغاً إذا لم ترغب في التغيير" class="input-field">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">تغيير الصورة الشخصية</label>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-slate-300 rounded-xl w-full text-sm text-slate-500">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn-secondary">إلغاء</a>
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <i class="fa-solid fa-save"></i>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
