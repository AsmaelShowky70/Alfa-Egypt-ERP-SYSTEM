@extends('layouts.app')

@section('title', 'تفاصيل الطلبية: ' . $order->order_number)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Status Banner -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <img src="{{ $order->customer->logo_url }}" alt="{{ $order->customer->name }}" class="w-16 h-16 rounded-2xl object-contain bg-slate-50 p-2 border border-slate-200 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-extrabold text-slate-500">{{ $order->order_number }}</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $order->status_badge['color'] }}">
                        {{ $order->status_badge['label'] }}
                    </span>
                </div>
                <h1 class="text-xl font-black text-slate-900 mt-0.5">{{ $order->customer->name }}</h1>
                <p class="text-xs text-slate-400">تاريخ الطلب: {{ $order->order_date->format('Y-m-d') }} | مندوب المبيعات: {{ $order->salesRep->name ?? 'أبانوب مجدي' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('print.salesInvoice', $order->id) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة فاتورة العميل</span>
            </a>
            @if(in_array($order->status, ['operations_scheduled', 'procurement_delayed', 'pending_operations']))
                <button onclick="document.getElementById('confirmDeliveryModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double"></i>
                    <span>تأكيد الميعاد النهائي وإبلاغ العميل</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Loss Alarm Warning Alert if applicable -->
    @if($order->has_loss_alarm)
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center gap-2 text-rose-700 font-black text-sm">
                <i class="fa-solid fa-triangle-exclamation text-lg animate-bounce"></i>
                <span>جرس إنذار نشط (Loss Alarm): هذا العقد يباع بسعر ثابت أقل من تكلفة الخامات المباشرة!</span>
            </div>
            <p class="text-xs text-rose-900 leading-relaxed">
                التكلفة الإجمالية المقدرة للخامات المباشرة هي <b>{{ number_format($order->total_estimated_cost, 2) }} ج.م</b> بينما إجمالي البيع التعاقدي هو <b>{{ number_format($order->subtotal, 2) }} ج.م</b> (عجز وخسارة بقيمة <b>{{ number_format($order->total_estimated_cost - $order->subtotal, 2) }} ج.م</b>). تم تسجيل الإنذار للإدارة العليا لحساب تكلفة المصانع وإعادة تسعير العقد مستقبلاً.
            </p>
        </div>
    @endif

    <!-- THE INTEGRATED ADMINISTRATIVE CYCLE STEPPER (الهيكل الإداري ودورة العمل التفاعلية) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-spin text-brand-600"></i>
                    <span>مخطط دورة العمل الإدارية التفاعلية للطلب</span>
                </h2>
                <p class="text-xs text-slate-500">حالة الربط اللحظي بين أقسام: المبيعات ← التشغيل ← المخزن ← المشتريات ← التسليم</p>
            </div>
            <span class="text-xs font-bold text-slate-400">
                المدة الإجمالية: <b>{{ ($order->operations_lead_time_days ?? 0) + ($order->procurement_lead_time_days ?? 0) }} أيام</b>
            </span>
        </div>

        <!-- Visual Stepper Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stage 1: Sales -->
            <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-emerald-800">1. قسم المبيعات</span>
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                </div>
                <div class="text-xs text-slate-700">تم تسجيل الطلب بتركيبة الكوڤير وإرساله لإدارة التشغيل.</div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $order->order_date->format('Y-m-d') }}</div>
            </div>

            <!-- Stage 2: Operations Planning -->
            @php
                $isOpsDone = in_array($order->status, ['operations_scheduled', 'confirmed', 'in_production', 'ready_in_warehouse', 'completed']);
            @endphp
            <div class="p-4 rounded-2xl border {{ $isOpsDone ? 'border-blue-200 bg-blue-50/50' : 'border-amber-200 bg-amber-50/50' }} space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-blue-800">2. إدارة التشغيل</span>
                    @if($isOpsDone)
                        <i class="fa-solid fa-circle-check text-blue-600 text-sm"></i>
                    @else
                        <i class="fa-solid fa-spinner text-amber-600 animate-spin text-sm"></i>
                    @endif
                </div>
                <div class="text-xs text-slate-700">
                    @if($isOpsDone)
                        تم عمل أمر التشغيل وتحديد مدة العمل: <b>{{ $order->operations_lead_time_days }} أيام</b>.
                    @else
                        قيد فحص المستلزمات وتقدير ساعات العمل على الماكينة.
                    @endif
                </div>
            </div>

            <!-- Stage 3: Warehouse & Procurement Check -->
            @php
                $isProcDone = ($order->procurement_lead_time_days > 0 || $order->status === 'confirmed' || $isOpsDone);
            @endphp
            <div class="p-4 rounded-2xl border {{ $order->procurement_lead_time_days > 0 ? 'border-purple-200 bg-purple-50/50' : 'border-slate-200 bg-slate-50/50' }} space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-purple-800">3. المخزن والمشتريات</span>
                    @if($order->procurement_lead_time_days > 0)
                        <i class="fa-solid fa-circle-check text-purple-600 text-sm"></i>
                    @else
                        <i class="fa-solid fa-circle-check text-slate-400 text-sm"></i>
                    @endif
                </div>
                <div class="text-xs text-slate-700">
                    @if($order->procurement_lead_time_days > 0)
                        تم حصر النواقص وأفادت المشتريات بتوفيرها خلال: <b>{{ $order->procurement_lead_time_days }} أيام</b>.
                    @else
                        كافة الخامات متوفرة بالمخزن المركزي ولا يوجد تأخير مشتريات.
                    @endif
                </div>
            </div>

            <!-- Stage 4: Final Confirmation & Production -->
            @php
                $isConfirmed = in_array($order->status, ['confirmed', 'in_production', 'ready_in_warehouse', 'completed']);
            @endphp
            <div class="p-4 rounded-2xl border {{ $isConfirmed ? 'border-brand-300 bg-brand-50/50' : 'border-slate-200 bg-slate-50/50' }} space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-brand-800">4. الرد على العميل والتسليم</span>
                    @if($isConfirmed)
                        <i class="fa-solid fa-circle-check text-brand-600 text-sm"></i>
                    @else
                        <i class="fa-solid fa-clock text-slate-400 text-sm"></i>
                    @endif
                </div>
                <div class="text-xs text-slate-700">
                    @if($order->confirmed_delivery_date)
                        الموعد المعتمد للعميل: <b>{{ $order->confirmed_delivery_date->format('Y-m-d') }}</b>.
                    @else
                        بانتظار اعتماد المبيعات للموعد النهائي وإبلاغ العميل.
                    @endif
                </div>
            </div>
        </div>

        @if($order->internal_notes)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                <span class="font-bold text-slate-800 block">سجل الإفادات والملاحظات الإدارية:</span>
                <p class="whitespace-pre-line leading-relaxed font-sans text-slate-700">{{ $order->internal_notes }}</p>
            </div>
        @endif
    </div>

    <!-- Items & Pricing Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">بنود الطلبية وتفاصيل أسعار الكوڤير</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">المنتج / طقم الكوڤير</th>
                        <th class="py-3 px-4">الكمية</th>
                        <th class="py-3 px-4">سعر بيع القطعة</th>
                        <th class="py-3 px-4">تكلفة خامات القطعة (BOM)</th>
                        <th class="py-3 px-4">إجمالي البيع</th>
                        <th class="py-3 px-4">إجمالي التكلفة</th>
                        <th class="py-3 px-4">موقف الربحية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-extrabold text-slate-900 text-sm">{{ $item->product->name }}</div>
                                @if($item->customization_notes)
                                    <div class="text-[11px] text-slate-400">{{ $item->customization_notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-black text-slate-800">{{ number_format($item->quantity) }} ق</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($item->unit_price, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-bold text-slate-500">{{ number_format($item->unit_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ number_format($item->subtotal, 2) }} ج.م</td>
                            <td class="py-3 px-4 font-bold text-slate-600">{{ number_format($item->total_cost, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                @if($item->has_loss_item)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-700">
                                        خسارة ({{ number_format($item->unit_cost - $item->unit_price, 2) }} ج.م/ق)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        ربح ({{ number_format($item->unit_price - $item->unit_cost, 2) }} ج.م/ق)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50/70 border-t-2 border-slate-200 text-xs font-bold">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-left">المجموع قبل الضريبة:</td>
                        <td class="py-3 px-4 text-slate-900 font-black">{{ number_format($order->subtotal, 2) }} ج.م</td>
                        <td class="py-3 px-4 text-slate-600">{{ number_format($order->total_estimated_cost, 2) }} ج.م</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-left">ضريبة القيمة المضافة (14%):</td>
                        <td class="py-3 px-4 text-slate-900">{{ number_format($order->tax, 2) }} ج.م</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="text-sm bg-slate-100 font-black text-brand-900">
                        <td colspan="4" class="py-3.5 px-4 text-left">المبلغ الإجمالي النهائي للطلبية:</td>
                        <td class="py-3.5 px-4 text-emerald-800">{{ number_format($order->total_amount, 2) }} ج.م</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Modal to Confirm Delivery Date with Customer -->
    <div id="confirmDeliveryModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-phone text-emerald-600"></i>
                    <span>تأكيد الموعد النهائي وإبلاغ العميل</span>
                </h3>
                <button onclick="document.getElementById('confirmDeliveryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('sales.orders.confirm_delivery', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الموعد النهائي المعتمد للتسليم</label>
                    <input type="date" name="confirmed_delivery_date" value="{{ $order->confirmed_delivery_date ? $order->confirmed_delivery_date->format('Y-m-d') : date('Y-m-d', strtotime('+4 days')) }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات الاتصال بالعميل</label>
                    <textarea name="internal_notes" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-emerald-600 outline-none" placeholder="تم الاتصال بمسؤول المشتريات وتأكيد استلام الطلبية يوم...">تم الاتصال بالعميل وإبلاغه بالموعد النهائي المعتمد للتسليم بناءً على خطة الإنتاج والتوريد.</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('confirmDeliveryModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow">
                        تأكيد واعتماد الطلبية
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
