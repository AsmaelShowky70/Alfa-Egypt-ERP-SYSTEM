@extends('layouts.app')

@section('title', 'لوحة تحكم إدارة المخازن المركزية')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-amber-800 to-amber-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-right">
            <span class="bg-amber-500/20 text-amber-300 border border-amber-400/30 px-3 py-1 rounded-full text-xs font-bold inline-block">
                المستودعات والمخازن المركزية
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold">مرحباً يا جورج وليم (مدير المخازن)</h1>
            <p class="text-amber-100 text-sm max-w-xl">
                إدارة جرد المواد الخام والمنتج التام وقطع الغيار، إصدار وأرشفة سندات الصرف والاستلام القابلة للطباعة، وتوجيه طلبات الشراء للمشتريات عند نقص الأرصدة.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('warehouse.inspections.create') }}" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>محضر فحص واستلام</span>
            </a>
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-extrabold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-receipt"></i>
                <span>إصدار سند صرف / استلام جديد</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks.create') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>تسجيل الجرد اليومي</span>
            </a>
        </div>
    </div>

    <!-- Warehouse KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قيمة المواد الخام ومستلزمات الكوڤير</p>
                <h3 class="text-lg font-black text-slate-800">{{ number_format($rawMaterialsValue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قيمة المنتج التام الجاهز للعملاء</p>
                <h3 class="text-lg font-black text-emerald-700">{{ number_format($finishedGoodsValue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">قيمة قطع غيار الماكينات</p>
                <h3 class="text-lg font-black text-blue-700">{{ number_format($sparePartsValue, 2) }} <span class="text-xs font-medium text-slate-500">ج.م</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border {{ $lowStockMaterials->count() > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lowStockMaterials->count() > 0 ? 'bg-rose-100 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">أصناف أوشكت على النفاد (حد الطلب)</p>
                <h3 class="text-lg font-black {{ $lowStockMaterials->count() > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lowStockMaterials->count() }} <span class="text-xs font-medium text-slate-500">صنف</span></h3>
            </div>
        </div>
    </div>

    <!-- Material Requisitions From Operations Section -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                    <span>طلبات صرف المستلزمات الواردة من إدارة التشغيل</span>
                </h2>
                <p class="text-xs text-slate-500">صرف الخامات بإذن صرف رسمي مطبوع، أو إصدار طلب شراء للمشتريات في حال وجود نواقص</p>
            </div>
            <a href="{{ route('warehouse.materialRequisitions') }}" class="text-xs font-bold text-amber-600 hover:underline">عرض كافة الطلبات</a>
        </div>

        <div class="space-y-3">
            @forelse($pendingRequisitions as $req)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-xs bg-amber-100 text-amber-900 px-2.5 py-0.5 rounded-lg">{{ $req->req_number }}</span>
                            <span class="font-extrabold text-sm text-slate-800">أمر تشغيل: {{ $req->productionOrder->order_number }}</span>
                        </div>
                        <p class="text-xs text-slate-600">
                            المنتج: <b class="text-slate-900">{{ $req->productionOrder->product->name }}</b> — 
                            الكمية المخططة: <b class="text-amber-700">{{ number_format($req->productionOrder->planned_quantity) }} ق</b>
                        </p>
                        <p class="text-[11px] text-slate-400">طلب بواسطة: {{ $req->requestedByUser->name ?? 'مدير التشغيل' }} بتاريخ {{ $req->requested_at->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <form action="{{ route('warehouse.materialRequisitions.issue', $req->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                                <span>صرف الخامات فوراً وإصدار السند</span>
                            </button>
                        </form>
                        <a href="{{ route('warehouse.materialRequisitions.show', $req->id) }}" class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition">
                            فحص البنود
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    لا توجد طلبات صرف مستلزمات معلقة من التشغيل حالياً.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Stock Vouchers & Printable Receipts -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-amber-600"></i>
                    <span>سندات المخزن المؤرشفة (استلام وصرف وقابلة للطباعة)</span>
                </h2>
                <p class="text-xs text-slate-500">أرشفة كاملة لكل ورقة استلام وتسليم وتوافر الطباعة بنقرة واحدة</p>
            </div>
            <a href="{{ route('warehouse.vouchers') }}" class="text-xs font-bold text-amber-600 hover:underline">عرض كل السندات</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم السند</th>
                        <th class="py-3 px-4">نوع الإذن</th>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4">القيمة الإجمالية</th>
                        <th class="py-3 px-4">المستلم / المسلّم</th>
                        <th class="py-3 px-4 text-center">الطباعة والأرشفة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentVouchers as $v)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">{{ $v->voucher_number }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $v->type_arabic }}
                                </span>
                            </td>
                            <td class="py-3 px-4">{{ $v->voucher_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-black text-slate-800">{{ number_format($v->total_value, 2) }} ج.م</td>
                            <td class="py-3 px-4 text-slate-600">{{ $v->received_or_delivered_by ?? 'غير مسجل' }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('print.voucher', $v->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-print"></i>
                                    <span>طباعة ورقة السند</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
