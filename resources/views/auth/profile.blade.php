@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-brand-900 to-brand-700 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row items-center gap-6">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-4 border-emerald-400/60 shadow-lg">
        <div class="space-y-1 text-center sm:text-right">
            <div class="inline-block bg-emerald-500/20 text-emerald-300 text-xs px-3 py-1 rounded-full font-bold border border-emerald-400/30 mb-1">
                {{ $user->role_arabic_name }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $user->name }}</h1>
            <p class="text-emerald-100 text-sm font-medium">{{ $user->job_title }} — {{ $user->department }}</p>
            <p class="text-xs text-emerald-200/80">{{ $user->email }} @if($user->phone)| {{ $user->phone }}@endif</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-user-pen text-brand-600"></i>
            <span>تحديث البيانات الشخصية وكلمة المرور</span>
        </h2>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">الاسم بالكامل</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">رقم الهاتف / الموبايل</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="01xxxxxxxxx">
                </div>
            </div>

            <!-- Avatar Upload -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">تحديث الصورة الشخصية (البروفايل)</label>
                <div class="flex items-center gap-4">
                    <img src="{{ $user->avatar_url }}" class="w-14 h-14 rounded-xl object-cover border border-slate-300">
                    <input type="file" name="avatar" accept="image/*" class="text-xs text-slate-600 file:mr-0 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">يُفضل رفع صورة مربعة واضحة بصيغة JPG أو PNG بحجم أقل من 3 ميجابايت.</p>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-key text-amber-500"></i>
                    <span>تغيير كلمة المرور (اختياري)</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">كلمة المرور الجديدة</label>
                        <input type="password" name="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="اتركه فارغاً إذا كنت لا ترغب بالتغيير">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-600 outline-none transition" placeholder="أعد كتابة كلمة المرور">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
