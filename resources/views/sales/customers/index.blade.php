@extends('layouts.app')

@section('title', 'قائمة العملاء ومطاعم مصر')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-users text-brand-600"></i>
                <span>قائمة العملاء والشركات (اللوجوهات والتصاميم)</span>
            </h1>
            <p class="text-xs text-slate-500">إدارة عملاء شركة ألفا إيجبت، أسماء الشركات ولوجوهاتها وتخصيص تركيبات الكوڤير لكل عميل</p>
        </div>
        <a href="{{ route('sales.customers.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>إضافة عميل جديد</span>
        </a>
    </div>

    <!-- Customers Grid Cards with Real Logos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($customers as $c)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $c->logo_url }}" alt="{{ $c->name }}" class="w-14 h-14 rounded-2xl object-contain bg-slate-50 p-1 border border-slate-200 shadow-sm">
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900 leading-tight">{{ $c->name }}</h3>
                                <span class="font-mono text-xs text-slate-400 font-bold">{{ $c->code }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $c->status ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $c->status ? 'نشط' : 'متوقف' }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                        @if($c->contact_person)
                            <div class="flex items-center gap-2"><i class="fa-solid fa-user text-slate-400 text-xs w-4"></i> <span>المسؤول: <b>{{ $c->contact_person }}</b></span></div>
                        @endif
                        @if($c->phone)
                            <div class="flex items-center gap-2"><i class="fa-solid fa-phone text-slate-400 text-xs w-4"></i> <span dir="ltr">{{ $c->phone }}</span></div>
                        @endif
                        @if($c->address)
                            <div class="flex items-center gap-2 truncate"><i class="fa-solid fa-location-dot text-slate-400 text-xs w-4"></i> <span>{{ $c->address }}</span></div>
                        @endif
                        <div class="flex items-center gap-2"><i class="fa-solid fa-handshake text-slate-400 text-xs w-4"></i> <span>شروط السداد: <b>{{ $c->payment_terms }}</b></span></div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">
                        الطلبات: <b class="text-brand-700">{{ $c->sales_orders_count }}</b> | المنتجات: <b class="text-brand-700">{{ $c->products_count }}</b>
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('sales.customers.show', $c->id) }}" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-xl transition">
                            عرض المنتجات والطلب
                        </a>
                        <a href="{{ route('sales.customers.edit', $c->id) }}" class="text-slate-400 hover:text-slate-600 p-1.5">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
