@extends('layouts.app')

@section('title', 'مراكز الإنتاج والمصانع المستقلة - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">مراكز الإنتاج وخطوط التصنيع المستقلة</h1>
            <p class="text-sm text-slate-500 mt-1">متابعة الأداء التشغيلي والمالي لكل خط إنتاج كمصنع ومحفظة أرباح مستقلة (Profit Center) مضافاً إليها 14% هامش تصنيع داخلي</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold px-3 py-1.5 bg-primary-100 text-primary-800 rounded-xl flex items-center gap-1.5">
                <i class="fa-solid fa-industry"></i>
                <span>6 خطوط إنتاج وتصنيع</span>
            </span>
        </div>
    </div>

    <!-- Overview Info Banner -->
    <div class="bg-gradient-to-r from-primary-900 to-primary-800 text-white p-6 rounded-2xl shadow-sm relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <h2 class="text-xl font-bold mb-2">استراتيجية مراكز الربحية بهيكل الفا ايجبت</h2>
            <p class="text-primary-100 text-xs sm:text-sm leading-relaxed">
                كل خط تصنيع للمادة الخام (الوايبس، الجوانتيات، قص البلاستيك، الحقن، والرولات) يعمل كمركز تكلفة وأرباح مستقل يورد للمخزن المركزي بطلب شراء رسمي وتضاف نسبة <span class="font-bold text-amber-300 font-mono">14%</span> كتكلفة وهامش إنتاج داخلي، مما يمكن الإدارة العليا من معرفة ربحية كل مصنع بدقة، وتحديد إمكانية البيع للغير من فائض المخزون.
            </p>
        </div>
        <i class="fa-solid fa-microchip absolute -bottom-6 -left-6 text-white/10 text-9xl"></i>
    </div>

    <!-- Factory Cards Grid (6 Lines) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($factories as $fac)
        @php
            $stats = $factoryStats[$fac->id] ?? [
                'internalRevenue' => 0,
                'markup14Earned' => 0,
                'costs' => 0,
                'netProfit' => 0,
                'unitsToday' => 0,
                'avgEff' => 95.0,
                'downtimeToday' => 0,
                'activeOrdersCount' => 0,
            ];
            $isProfitable = $stats['netProfit'] >= 0;
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <!-- Header -->
                <div class="p-5 border-b border-slate-100 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $fac->code }}</span>
                            @if($fac->is_outsource)
                                <span class="badge badge-warning text-[10px]">
                                    <i class="fa-solid fa-handshake"></i>
                                    <span>تصنيع عند الغير</span>
                                </span>
                            @else
                                <span class="badge badge-success text-[10px]">
                                    <i class="fa-solid fa-building-circle-check"></i>
                                    <span>تصنيع داخلي</span>
                                </span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mt-2">{{ $fac->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $fac->description }}</p>
                    </div>

                    <div class="w-12 h-12 rounded-2xl {{ $isProfitable ? 'bg-primary-50 text-primary-700' : 'bg-rose-50 text-rose-700' }} flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid {{ $fac->is_outsource ? 'fa-truck-ramp-box' : 'fa-gears' }}"></i>
                    </div>
                </div>

                <!-- Financial Performance (Independent P&L) -->
                <div class="p-5 bg-slate-50/50 border-b border-slate-100 space-y-3 font-mono">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-sans">توريدات للمخزن المركزي (+14%):</span>
                        <span class="font-bold text-slate-800">{{ number_format($stats['internalRevenue'], 2) }} ج.م</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-sans">أرباح هامش التصنيع (14%):</span>
                        <span class="font-bold text-emerald-600">+{{ number_format($stats['markup14Earned'], 2) }} ج.م</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-sans">تكاليف التشغيل والخامات:</span>
                        <span class="font-bold text-rose-600">-{{ number_format($stats['costs'], 2) }} ج.م</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm font-bold">
                        <span class="font-sans text-slate-800">صافي ربح الخط المستقل:</span>
                        <span class="{{ $isProfitable ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $isProfitable ? '+' : '' }}{{ number_format($stats['netProfit'], 2) }} ج.م
                        </span>
                    </div>
                </div>

                <!-- Production & Operational KPIs -->
                <div class="p-5 space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 font-sans">إنتاج اليوم</span>
                            <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format($stats['unitsToday']) }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 font-sans">كفاءة التشغيل OEE</span>
                            <span class="font-mono font-bold text-primary-700 text-sm">{{ number_format($stats['avgEff'], 1) }}%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                        <span>الماكينات الملحقة: <strong class="text-slate-800">{{ $fac->machines->count() }} ماكينات</strong></span>
                        <span>أوامر نشطة: <strong class="text-slate-800">{{ $stats['activeOrdersCount'] }}</strong></span>
                    </div>

                    @if($stats['downtimeToday'] > 0)
                        <div class="text-[11px] text-amber-700 bg-amber-50 p-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>ساعات توقف اليوم: {{ $stats['downtimeToday'] }} دقيقة</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Action -->
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                <a href="{{ route('factories.show', $fac->id) }}" class="btn-secondary w-full text-center text-xs flex items-center justify-center gap-2">
                    <span>عرض تقرير الخط ومراقبة الماكينات</span>
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
