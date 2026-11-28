@extends('layouts.app')

@section('title', 'المخزن المركزي')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-amber-600"></i>
                <span>المستودع والمخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500">متابعة أرصدة المواد الخام ومستلزمات الكوڤير والمنتج التام وقطع الغيار وسندات الصرف والاستلام</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('warehouse.inspections.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>محضر فحص واستلام</span>
            </a>
            <a href="{{ route('warehouse.vouchers.create') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-receipt"></i>
                <span>إصدار سند صرف / استلام</span>
            </a>
            <a href="{{ route('warehouse.dailyChecks.create') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>الجرد اليومي</span>
            </a>
        </div>
    </div>

    <!-- Inventory 3 Core Categories Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 1. Raw Materials -->
        <a href="{{ route('warehouse.rawMaterials') }}" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 group">
            <div class="flex items-center justify-between">
                <span class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl group-hover:scale-105 transition">
                    <i class="fa-solid fa-cubes-stacked"></i>
                </span>
                <span class="text-xs font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-xl">
                    {{ $rawMaterials->count() }} صنف
                </span>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900 group-hover:text-amber-700 transition">المواد الخام ومستلزمات الكوڤير</h3>
                <p class="text-xs text-slate-400 mt-0.5">معالق، مناديل بيور، خلة، مناديل معطرة، سيلوفان</p>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">إجمالي القيمة:</span>
                <span class="font-black text-slate-900 text-sm">{{ number_format($rawMaterialsValue, 2) }} ج.م</span>
            </div>
        </a>

        <!-- 2. Finished Goods -->
        <a href="{{ route('warehouse.finishedGoods') }}" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 group">
            <div class="flex items-center justify-between">
                <span class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl group-hover:scale-105 transition">
                    <i class="fa-solid fa-box-open"></i>
                </span>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl">
                    {{ $finishedGoods->count() }} صنف
                </span>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900 group-hover:text-emerald-700 transition">المنتج التام (الكوڤير والأكياس)</h3>
                <p class="text-xs text-slate-400 mt-0.5">كوڤير أبو طارق، كوڤير 2*1، علب جوانتيات، باكت وايبس</p>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">إجمالي القيمة:</span>
                <span class="font-black text-emerald-800 text-sm">{{ number_format($finishedGoodsValue, 2) }} ج.م</span>
            </div>
        </a>

        <!-- 3. Spare Parts -->
        <a href="{{ route('warehouse.spareParts') }}" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 group">
            <div class="flex items-center justify-between">
                <span class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl group-hover:scale-105 transition">
                    <i class="fa-solid fa-wrench"></i>
                </span>
                <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-xl">
                    {{ $spareParts->count() }} صنف
                </span>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900 group-hover:text-blue-700 transition">قطع غيار الماكينات والسيور</h3>
                <p class="text-xs text-slate-400 mt-0.5">سيور ناقلة، سخانات لحام، سكاكين تقطيع فولاذية</p>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">إجمالي القيمة:</span>
                <span class="font-black text-blue-800 text-sm">{{ number_format($sparePartsValue, 2) }} ج.م</span>
            </div>
        </a>
    </div>

    <!-- Low Stock Materials Warnings Table -->
    @if($lowStockMaterials->count() > 0)
    <div class="bg-rose-50 border border-rose-200 rounded-3xl p-6 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-rose-950 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 animate-pulse"></i>
                <span>تنبيه: أصناف بلغت حد إعادة الطلب بالمخزن (تتطلب طلب شراء)</span>
            </h2>
            <a href="{{ route('purchasing.orders.create') }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition">
                إصدار طلب شراء للمشتريات
            </a>
        </div>

        <div class="overflow-x-auto bg-white rounded-2xl border border-rose-200">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-rose-100/50 text-rose-900 font-bold border-b border-rose-200">
                        <th class="py-2.5 px-4">كود الصنف</th>
                        <th class="py-2.5 px-4">اسم الخامة</th>
                        <th class="py-2.5 px-4">الرصيد المتبقي</th>
                        <th class="py-2.5 px-4">حد الطلب الآمن</th>
                        <th class="py-2.5 px-4">المورد المعتمد</th>
                        <th class="py-2.5 px-4">الموقع بالمخزن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-100">
                    @foreach($lowStockMaterials as $lsm)
                        <tr>
                            <td class="py-2.5 px-4 font-mono font-bold">{{ $lsm->code }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900">{{ $lsm->name }}</td>
                            <td class="py-2.5 px-4 font-black text-rose-700">{{ number_format($lsm->current_stock) }} {{ $lsm->unit }}</td>
                            <td class="py-2.5 px-4 text-slate-600">{{ number_format($lsm->reorder_level) }} {{ $lsm->unit }}</td>
                            <td class="py-2.5 px-4 text-slate-700">{{ $lsm->supplier->name ?? 'تصنيع خط داخلي' }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $lsm->warehouse_location ?? 'المخزن العام' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Material Requisitions From Operations Section -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                <span>طلبات صرف خامات الإنتاج الواردة من إدارة التشغيل ({{ $pendingRequisitions->count() }})</span>
            </h2>
            <a href="{{ route('warehouse.materialRequisitions') }}" class="text-xs font-bold text-amber-600 hover:underline">عرض كل طلبات الصرف</a>
        </div>

        <div class="space-y-3">
            @forelse($pendingRequisitions as $req)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-xs bg-amber-100 text-amber-900 px-2 py-0.5 rounded">{{ $req->req_number }}</span>
                            <span class="font-extrabold text-sm text-slate-900">أمر تشغيل: {{ $req->productionOrder->order_number }}</span>
                        </div>
                        <p class="text-xs text-slate-600">المنتج: <b class="text-slate-800">{{ $req->productionOrder->product->name }}</b> بمقدار <b class="text-amber-800">{{ number_format($req->productionOrder->planned_quantity) }} قطعة</b></p>
                    </div>

                    <div class="flex items-center gap-2">
                        <form action="{{ route('warehouse.materialRequisitions.issue', $req->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                                <span>صرف الخامات وإصدار السند</span>
                            </button>
                        </form>
                        <a href="{{ route('warehouse.materialRequisitions.show', $req->id) }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                            تفاصيل
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    لا توجد طلبات صرف معلقة حالياً.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
