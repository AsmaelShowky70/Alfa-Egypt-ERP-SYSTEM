@extends('layouts.app')

@section('title', 'مخزن المنتج التام')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-emerald-600"></i>
                <span>مخزن المنتج التام (الكوڤير، علب الجوانتيات، باكت الوايبس)</span>
            </h1>
            <p class="text-xs text-slate-500">جاهز للتسليم للعملاء المتعاقدين أو البيع للغير من المخزن مباشرة بعد معرفة التكلفة</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-receipt"></i>
                <span>سند تسليم لعميل / بيع للغير</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                ← رجوع للمخزن
            </a>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($products as $p)
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono text-xs font-bold text-slate-400">{{ $p->code }}</span>
                            <h3 class="font-black text-base text-slate-900 mt-0.5">{{ $p->name }}</h3>
                            @if($p->customer)
                                <p class="text-xs font-bold text-brand-700 mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>مخصص لعميل: {{ $p->customer->name }}</span>
                                </p>
                            @else
                                <p class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-store"></i>
                                    <span>متاح للبيع للغير من المخزن</span>
                                </p>
                            @endif
                        </div>
                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-16 h-16 rounded-xl object-contain bg-slate-50 p-1 border border-slate-200">
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2">{{ $p->description }}</p>

                    <!-- Stock & Cost Card -->
                    <div class="grid grid-cols-2 gap-2 text-center text-xs bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">الرصيد التام بالمخزن</span>
                            <span class="font-black text-slate-900 text-sm">{{ number_format($p->current_stock) }} {{ $p->unit }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">إجمالي القيمة المخزنية</span>
                            <span class="font-black text-emerald-800 text-sm">{{ number_format($p->current_stock * $p->cost_price, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">تكلفة القطعة (BOM)</span>
                        <span class="font-black text-slate-700">{{ number_format($p->cost_price, 2) }} ج.م</span>
                    </div>
                    <div class="text-left">
                        <span class="text-[10px] text-slate-400 block">سعر البيع المعتمد</span>
                        <span class="font-black text-brand-700 text-sm">{{ number_format($p->selling_price, 2) }} ج.م</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
