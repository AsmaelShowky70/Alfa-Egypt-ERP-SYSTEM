@extends('layouts.app')

@section('title', 'إنذارات مراقبة خسائر الكوڤير - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-rose-100 text-rose-600 rounded-2xl animate-pulse">
                    <i class="fa-solid fa-bell-exclamation text-xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">جرس إنذار ومراقبة خسائر الكوڤير (Loss Alarms)</h1>
                    <p class="text-sm text-slate-500 mt-0.5">رصد فوري لطلبات الكوڤير المباعة بأقل من التكلفة المباشرة للوفاء بعقود العملاء وتحديد المشكلة</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.pricing') }}" class="btn-secondary flex items-center gap-2">
                <i class="fa-solid fa-calculator"></i>
                <span>محرك التسعير و الـ BOM</span>
            </a>
            <a href="{{ route('finance.index') }}" class="btn-secondary flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                <span>الإدارة المالية</span>
            </a>
        </div>
    </div>

    <!-- Executive Insight Alert -->
    <div class="p-5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-rose-200">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
        </div>
        <div>
            <h3 class="font-bold text-rose-900 text-base">توجيه الإدارة العليا وسياسة استمرارية التوريد</h3>
            <p class="text-sm text-rose-800/90 mt-1 leading-relaxed">
                بناءً على تعليمات السيد رئيس مجلس الإدارة والمدير العام: <strong class="underline">الكوڤير حتى لو كان يخسر؛ سعره مستمر وثابت في العقود الموقعة لحماية سمعة الفا ايجبت أمام كبار العملاء</strong>، ولكن يظهر هذا الإنذار فوراً لإدارة الحسابات والتشغيل والمدير العام لتحديد مصدر الزيادة (تغير سعر المعلقة، المناديل، الخلة، أو الغلاف) ورفع تقرير بالإجراء التصحيحي لتعديل السعر مستقبلاً عند تجديد العقد.
            </p>
        </div>
    </div>

    <!-- Loss Orders List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-rose-600"></i>
                <span>الطلبيات النشطة تحت جرس الإنذار ({{ $lossOrders->count() }})</span>
            </h2>
            <span class="text-xs font-bold px-3 py-1 bg-rose-100 text-rose-800 rounded-full">
                يتطلب مراجعة تكلفة الخامات فوراً
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($lossOrders as $order)
            @php
                $orderTotalLoss = 0;
            @endphp
            <div class="p-6 hover:bg-slate-50/60 transition-colors">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        @if($order->customer?->logo)
                            <img src="{{ asset($order->customer->logo) }}" class="w-14 h-14 rounded-2xl object-contain border border-slate-200 p-1 bg-white shadow-sm" alt="">
                        @else
                            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xl">
                                <i class="fa-solid fa-building"></i>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-lg">{{ $order->customer?->name ?? 'عميل غير محدد' }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700">{{ $order->order_number }}</span>
                                <span class="badge badge-danger">
                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                    <span>إنذار بيع بأقل من التكلفة</span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                تاريخ الأوردر: <span class="font-mono text-slate-700">{{ $order->order_date?->format('Y-m-d') }}</span> | 
                                موعد التسليم: <span class="font-mono text-slate-700">{{ $order->confirmed_delivery_date?->format('Y-m-d') ?? 'قيد التحديد' }}</span> | 
                                الحالة: <span class="font-semibold text-primary-700">{{ $order->status }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('sales.orders.show', $order->id) }}" class="btn-secondary text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-eye"></i>
                            <span>تفاصيل أمر البيع</span>
                        </a>
                        <a href="{{ route('print.salesInvoice', $order->id) }}" target="_blank" class="btn-secondary text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-print"></i>
                            <span>طباعة الفاتورة</span>
                        </a>
                    </div>
                </div>

                <!-- Breakdown of Items in this order causing loss -->
                <div class="bg-rose-50/40 rounded-xl border border-rose-100 p-4">
                    <h4 class="text-xs font-bold text-rose-900 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-check"></i>
                        <span>تحليل التكلفة وسعر البيع للأصناف المدرجة في هذا الطلب:</span>
                    </h4>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="text-slate-500 font-bold border-b border-rose-200/60 pb-2">
                                    <th class="py-2 px-3">المنتج والوصف</th>
                                    <th class="py-2 px-3">الكمية المطلوبة</th>
                                    <th class="py-2 px-3">التكلفة المباشرة للوحدة</th>
                                    <th class="py-2 px-3">سعر البيع التعاقدي</th>
                                    <th class="py-2 px-3">فرق الخسارة للقطعة</th>
                                    <th class="py-2 px-3">إجمالي خسارة البند</th>
                                    <th class="py-2 px-3">أسباب الفجوة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rose-100/60 font-mono">
                                @foreach($order->items as $item)
                                @php
                                    $unitDiff = $item->unit_price - $item->unit_cost;
                                    $itemTotalLoss = $unitDiff * $item->quantity;
                                    if ($unitDiff < 0) {
                                        $orderTotalLoss += abs($itemTotalLoss);
                                    }
                                @endphp
                                <tr>
                                    <td class="py-3 px-3 font-sans font-bold text-slate-800">
                                        {{ $item->product?->name ?? 'منتج' }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-700 font-bold">
                                        {{ number_format($item->quantity) }} {{ $item->product?->unit ?? 'قطعة' }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-800 font-bold">
                                        {{ number_format($item->unit_cost, 2) }} ج.م
                                    </td>
                                    <td class="py-3 px-3 text-slate-800 font-bold">
                                        {{ number_format($item->unit_price, 2) }} ج.م
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($unitDiff < 0)
                                            <span class="text-rose-600 font-bold">{{ number_format($unitDiff, 2) }} ج.م (خسارة)</span>
                                        @else
                                            <span class="text-emerald-600 font-bold">+{{ number_format($unitDiff, 2) }} ج.م (ربح)</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($itemTotalLoss < 0)
                                            <span class="text-rose-700 font-extrabold text-sm">{{ number_format(abs($itemTotalLoss), 2) }} ج.م-</span>
                                        @else
                                            <span class="text-emerald-700 font-bold">+{{ number_format($itemTotalLoss, 2) }} ج.م</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-sans text-[11px] text-slate-600">
                                        @if($unitDiff < 0)
                                            <span class="text-rose-800">سعر تعاقدي قديم ثابت رغم ارتفاع أسعار الخامات أو المنديل المعطر/المعلقة</span>
                                        @else
                                            <span class="text-emerald-800">يحقق ربحاً تشغيلياً مستهدفاً</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-t border-rose-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs gap-2">
                        <div class="text-rose-800 font-bold flex items-center gap-2">
                            <i class="fa-solid fa-chart-line-down"></i>
                            <span>إجمالي الخسارة التقديرية المحملة على أمر التوريد:</span>
                            <span class="font-mono text-base font-extrabold text-rose-700">{{ number_format($orderTotalLoss, 2) }} ج.م</span>
                        </div>
                        <div class="text-slate-500">
                            الإجراء الإداري: <span class="font-semibold text-slate-700">التوريد مستمر وفق العقد + جدولة تعديل السعر في العقد القادم</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-shield-check text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-base">لا توجد أي طلبيات مباعة بخسارة حالياً!</h3>
                <p class="text-xs text-slate-500 mt-1">جميع أوامر البيع تحقق هوامش ربحية أعلى من تكلفة الإنتاج والخامات المباشرة.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
