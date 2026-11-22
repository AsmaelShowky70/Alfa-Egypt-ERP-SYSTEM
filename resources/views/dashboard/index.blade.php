@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm text-center space-y-3">
        <h1 class="text-2xl font-black text-slate-800">مرحباً بك في نظام الفا ايجبت ERP</h1>
        <p class="text-sm text-slate-500">تم تسجيل دخولك بنجاح كـ {{ Auth::user()->role_arabic_name }}</p>
    </div>
</div>
@endsection
