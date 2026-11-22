@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة الصيانة والمعدات')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-orange-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-orange-500/20 text-orange-300 border border-orange-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                الصيانة الهندسية والمرافق
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا فادي أشرف (مدير الصيانة)</h1>
            <p class="text-orange-100 text-sm max-w-xl">
                متابعة الحالة التشغيلية لماكينات خطوط الإنتاج والسيور، أرصدة قطع الغيار في المخزن المركزي، وتحليل أسباب التوقفات المسجلة في تقارير الساعات.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('maintenance.index') }}" class="px-5 py-2.5 bg-orange-400 hover:bg-orange-300 text-orange-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-wrench"></i>
                <span>سجل الماكينات والجاهزية</span>
            </a>
            <a href="{{ route('warehouse.spareParts') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>مخزون قطع الغيار</span>
            </a>
        </div>
    </div>

    <!-- Maintenance KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">ماكينات تعمل بحالة ممتازة</p>
                <h3 class="text-xl font-black text-emerald-700">{{ $runningMachines }} <span class="text-xs font-medium text-slate-500">ماكينة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">ماكينات تتطلب صيانة دورية</p>
                <h3 class="text-xl font-black text-orange-700">{{ $maintenanceDueMachines }} <span class="text-xs font-medium text-slate-500">ماكينة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-toolbox"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">أنواع قطع الغيار المتوفرة</p>
                <h3 class="text-xl font-black text-slate-800">{{ $spareParts->count() }} <span class="text-xs font-medium text-slate-500">بند</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border {{ $lowSpareParts->count() > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lowSpareParts->count() > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قطع غيار أوشكت على النفاد</p>
                <h3 class="text-xl font-black {{ $lowSpareParts->count() > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lowSpareParts->count() }} <span class="text-xs font-medium text-slate-500">بند</span></h3>
            </div>
        </div>
    </div>

    <!-- Machines Grid -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-industry text-orange-600"></i>
                <span>جاهزية ماكينات وخطوط ألفا إيجبت</span>
            </h2>
            <a href="{{ route('maintenance.index') }}" class="text-xs font-bold text-orange-600 hover:underline">عرض التفاصيل الكاملة</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($machines as $m)
                <div class="p-4 rounded-2xl border border-slate-200/90 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-slate-400">{{ $m->code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->status === 'running' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $m->status === 'running' ? 'تعمل بكفاءة' : 'في الصيانة' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm text-slate-800">{{ $m->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $m->factory->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-center text-xs bg-white p-2 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block">السرعة المعيارية</span>
                            <span class="font-black text-slate-800">{{ number_format($m->rated_speed_units_per_hour) }} ق/س</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">سرعة السير</span>
                            <span class="font-black text-slate-800">{{ $m->belt_speed_m_min }} م/د</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
