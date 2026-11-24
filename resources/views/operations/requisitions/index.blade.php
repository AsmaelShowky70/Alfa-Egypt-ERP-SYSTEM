@extends('layouts.app')

@section('title', 'طلبات واستلام خامات الإنتاج من المخزن المركزي')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                <span>طلبات واستلام خامات الإنتاج من المخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500">متابعة خروج مستلزمات الإنتاج من المخزن وتأكيد استلامها الفعلي على خطوط الإنتاج بالمصانع</p>
        </div>
        <button onclick="document.getElementById('newRequisitionModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إنشاء طلب صرف خامات جديد</span>
        </button>
    </div>

    <!-- Summary KPI Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي طلبات الصرف</p>
                <h3 class="text-xl font-black text-slate-900">{{ $requisitions->total() }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">بانتظار موافقة وصرف المخزن</p>
                <h3 class="text-xl font-black text-amber-700">{{ $pendingCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-sm flex items-center gap-4 bg-gradient-to-l from-blue-50/50 to-white">
            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl flex-shrink-0 shadow">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-blue-600">تم الصرف (بانتظار استلامك)</p>
                <h3 class="text-xl font-black text-blue-800">{{ $inTransitCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">تم استلامها بالمصنع</p>
                <h3 class="text-xl font-black text-emerald-700">{{ $receivedCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Requisitions List -->
    <div class="space-y-4">
        @forelse($requisitions as $req)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-black text-sm flex-shrink-0">
                            MR
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $req->req_number }}</h3>
                                <span class="bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $req->productionOrder?->order_number ?? 'طلب مباشر' }}
                                </span>
                                <span class="px-3 py-0.5 rounded-full text-xs font-black border {{ $req->status_badge['color'] }}">
                                    {{ $req->status_badge['label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                المنتج المستهدف: <b class="text-slate-800">{{ $req->productionOrder?->product?->name ?? 'غير محدد' }}</b>
                                @if($req->productionOrder?->salesOrder?->customer)
                                    | العميل: <b class="text-brand-700">{{ $req->productionOrder->salesOrder->customer->name }}</b>
                                @endif
                                | تاريخ الطلب: {{ $req->requested_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    <!-- Action: Confirm Receipt if Issued -->
                    <div class="flex items-center gap-3">
                        @if($req->status === 'issued')
                            <form action="{{ route('operations.requisitions.confirmReceipt', $req->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-600/30 hover:shadow-xl transition flex items-center gap-2">
                                    <i class="fa-solid fa-box-open"></i>
                                    <span>تأكيد استلام الخامات على خط الإنتاج بالمصنع</span>
                                </button>
                            </form>
                        @elseif($req->status === 'received')
                            <div class="text-left text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3.5 py-2 rounded-xl flex items-center gap-2">
                                <i class="fa-solid fa-check-double text-emerald-600"></i>
                                <span>تم الاستلام بواسطة: <b>{{ $req->receivedByUser?->name ?? 'مدير التشغيل' }}</b> في {{ $req->received_at?->format('Y-m-d H:i') }}</span>
                            </div>
                        @else
                            <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-clock"></i>
                                <span>الطلب قيد المراجعة والصرف من أمين المخزن</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Items Grid -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold">
                                <th class="py-2.5 px-3">الخامة المطلوبة</th>
                                <th class="py-2.5 px-3">كود الخامة</th>
                                <th class="py-2.5 px-3">الكمية المطلوبة</th>
                                <th class="py-2.5 px-3">الكمية المنصرفة فعلياً</th>
                                <th class="py-2.5 px-3">موقف التوفر</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @foreach($req->items as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-sans font-bold text-slate-800">
                                        {{ $item->rawMaterial?->name ?? 'خامة' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                        {{ $item->rawMaterial?->code ?? '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-black text-slate-900">
                                        {{ number_format($item->quantity_requested) }} <span class="text-[10px] text-slate-400 font-sans">{{ $item->rawMaterial?->unit ?? 'قطعة' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-black text-emerald-700">
                                        {{ number_format($item->quantity_issued) }} <span class="text-[10px] text-slate-400 font-sans">{{ $item->rawMaterial?->unit ?? 'قطعة' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-sans">
                                        @if($item->quantity_missing > 0)
                                            <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px] font-bold">عجز {{ number_format($item->quantity_missing) }}</span>
                                        @else
                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-bold">متوفرة ومصروفة</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($req->notes)
                    <div class="text-xs bg-slate-50 text-slate-600 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-comment-dots text-slate-400"></i>
                        <span><b>ملاحظات الطلب:</b> {{ $req->notes }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center text-slate-400 text-xs border border-slate-200">
                لا توجد طلبات صرف خامات مسجلة حالياً.
            </div>
        @endforelse

        <div class="pt-4">
            {{ $requisitions->links() }}
        </div>
    </div>

    <!-- Modal to submit new material requisition -->
    <div id="newRequisitionModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-dolly text-blue-600"></i>
                    <span>إنشاء طلب صرف خامات من المخزن المركزي</span>
                </h3>
                <button onclick="document.getElementById('newRequisitionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('operations.requisitions.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">أمر التشغيل المرتبط *</label>
                    <select name="production_order_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        @foreach($activeProductionOrders as $wo)
                            <option value="{{ $wo->id }}">
                                {{ $wo->order_number }} — {{ $wo->product?->name }} ({{ $wo->factory?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2 border-t border-slate-100 pt-3">
                    <label class="block text-xs font-bold text-slate-700">الخامات والمستلزمات المطلوبة صرفها:</label>
                    
                    <div id="itemsContainer" class="space-y-2">
                        <div class="grid grid-cols-12 gap-2 item-row">
                            <div class="col-span-8">
                                <select name="items[0][raw_material_id]" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-blue-600 outline-none">
                                    <option value="">-- اختر الخامة المطلوبة --</option>
                                    @foreach($rawMaterials as $rm)
                                        <option value="{{ $rm->id }}">{{ $rm->name }} (المتاح: {{ number_format($rm->current_stock) }} {{ $rm->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-4">
                                <input type="number" step="1" name="items[0][quantity]" placeholder="الكمية المطلوبة" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addItemRow()" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 pt-1">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>إضافة خامة أخرى للطلب</span>
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات / تعليمات الصرف</label>
                    <input type="text" name="notes" placeholder="مثال: صرف عاجل لتغطية زيادة إنتاج الوردية..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('newRequisitionModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow">
                        إرسال طلب الصرف للمخزن المركزي
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    let rowIndex = 1;
    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        
        const select = newRow.querySelector('select');
        const input = newRow.querySelector('input');
        
        select.name = `items[${rowIndex}][raw_material_id]`;
        select.value = '';
        input.name = `items[${rowIndex}][quantity]`;
        input.value = '';
        
        container.appendChild(newRow);
        rowIndex++;
    }
</script>
@endsection