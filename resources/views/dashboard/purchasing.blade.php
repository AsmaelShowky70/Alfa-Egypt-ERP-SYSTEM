@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة المشتريات')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-purple-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-purple-500/20 text-purple-300 border border-purple-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                إدارة المشتريات والتوريدات
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا مؤمن عاطف (مدير المشتريات)</h1>
            <p class="text-purple-100 text-sm max-w-xl">
                استقبال طلبات النواقص من المخزن المركزي، تحديد المدة الزمنية لتوفير الخامات للتأثير على موعد تسليم المبيعات، وإصدار أوامر الشراء الخارجية والداخلية (+14% تكاليف إنتاج).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.orders.create') }}" class="px-5 py-2.5 bg-purple-400 hover:bg-purple-300 text-purple-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إصدار أمر شراء جديد (+14%)</span>
            </a>
            <a href="{{ route('purchasing.suppliers') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-handshake"></i>
                <span>الموردون وجهات التصنيع</span>
            </a>
        </div>
    </div>

    <!-- Purchasing KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-bell-concierge"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">طلبات نواقص المخزن المعلقة</p>
                <h3 class="text-xl font-black text-purple-700">{{ $pendingRequisitions->count() }} <span class="text-xs font-medium text-slate-500">طلب</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">أوامر شراء جارية للتوريد</p>
                <h3 class="text-xl font-black text-blue-700">{{ $activePurchaseOrders->count() }} <span class="text-xs font-medium text-slate-500">أمر</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي المشتريات المستلمة</p>
                <h3 class="text-xl font-black text-emerald-700">{{ number_format($totalSpend, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">الموردون والشركاء المعتمدون</p>
                <h3 class="text-xl font-black text-amber-700">{{ $suppliers->count() }} <span class="text-xs font-medium text-slate-500">مورد</span></h3>
            </div>
        </div>
    </div>

    <!-- Missing Material Requisitions Awaiting Lead Time Response -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                    <span>طلبات النواقص من المخزن (مطلوب تحديد مدة التوريد للتشغيل والمبيعات)</span>
                </h2>
                <p class="text-xs text-slate-500">إدخال المدة التي ستوفر بها المشتريات الخامة لينعكس الموعد النهائي تلقائياً على المبيعات</p>
            </div>
            <a href="{{ route('purchasing.requisitions') }}" class="text-xs font-bold text-purple-600 hover:underline">عرض كل طلبات النواقص</a>
        </div>

        <div class="space-y-4">
            @forelse($pendingRequisitions as $pr)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs bg-purple-100 text-purple-900 px-2.5 py-0.5 rounded-lg">{{ $pr->pr_number }}</span>
                                <span class="font-extrabold text-sm text-slate-900">لصالح العميل: {{ $pr->salesOrder->customer->name ?? 'طلب مخزن عام' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">السبب: {{ $pr->reason }}</p>
                        </div>
                        <span class="text-xs bg-rose-100 text-rose-800 font-bold px-3 py-1 rounded-full">
                            أهمية: {{ $pr->urgency === 'critical' ? 'حرجة جداً' : 'عاجلة' }}
                        </span>
                    </div>

                    <!-- Items needed -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/80 text-xs">
                        <span class="font-bold text-slate-700 block mb-2">الخامات والمستلزمات الناقصة:</span>
                        <div class="space-y-1">
                            @foreach($pr->items as $item)
                                <div class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
                                    <span class="font-bold text-slate-800">{{ $item->rawMaterial->name }}</span>
                                    <span>الكمية المطلوبة: <b class="text-purple-700">{{ number_format($item->needed_quantity) }} {{ $item->rawMaterial->unit }}</b> (الرصيد الحالي بالمخزن: {{ number_format($item->current_stock_at_request) }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Lead Time Response Form -->
                    <form action="{{ route('purchasing.requisitions.leadTime', $pr->id) }}" method="POST" class="bg-white p-3 rounded-xl border border-slate-200 flex flex-wrap items-center gap-3">
                        @csrf
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">المدة المطلوبة للتوريد (أيام)</label>
                            <input type="number" name="procurement_lead_time_days" value="3" min="1" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none">
                        </div>

                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">التاريخ المتوقع لوصول الخامة</label>
                            <input type="date" name="estimated_arrival_date" value="{{ \Carbon\Carbon::now()->addDays(3)->format('Y-m-d') }}" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-purple-600 outline-none">
                        </div>

                        <div class="flex-2 min-w-[200px]">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">ملاحظات التوريد والمورد</label>
                            <input type="text" name="procurement_reply_notes" placeholder="تم التواصل مع المورد وجاري شحن الرولات..." required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-purple-600 outline-none">
                        </div>

                        <div class="w-full sm:w-auto self-end pt-1">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>إرسال الرد وتحديث موعد العميل</span>
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    لا توجد طلبات نواقص معلقة من المخزن حالياً.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
