<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة مبيعات عميل {{ $order->order_number }} - الفا ايجبت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 text-slate-800">

    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 font-bold text-sm shadow-sm hover:bg-slate-50 transition border border-slate-200">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-primary-700 text-white font-bold text-sm shadow-md hover:bg-primary-800 transition">
            <i class="fa-solid fa-print"></i>
            <span>طباعة فاتورة المبيعات (Print Invoice)</span>
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print-sheet">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol.png') }}" alt="Alfa Egypt" class="w-16 h-16 object-contain rounded-xl shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-red-600 font-bold">ALPHA EGYPT FOR MODERN PACKAGING INDUSTRIES</p>
                    <p class="text-[11px] text-slate-500 mt-1">المنطقة الصناعية | س.ت: 182736 | ب.ض: 549-382-109 | https://alphaegypterp.com</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">رقم الفاتورة</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-600 font-bold">{{ $order->order_number }}</span>
                </div>
            </div>
        </div>

        <div class="text-center my-6">
            <span class="inline-block px-8 py-2 rounded-xl text-lg font-black bg-primary-50 text-primary-900 border border-primary-200">
                فاتورة مبيعات ضريبية رسمية (Sales Invoice)
            </span>
        </div>

        <!-- Customer & Order Details Grid -->
        <div class="grid grid-cols-2 gap-6 p-5 bg-slate-50 rounded-2xl border border-slate-200 text-xs mb-6">
            <!-- Client Info with Logo -->
            <div class="space-y-2 border-l border-slate-200 pl-4">
                <span class="text-[11px] font-bold text-slate-400 block">بيانات العميل والمشتري:</span>
                <div class="flex items-center gap-3">
                    @if($order->customer?->logo)
                        <img src="{{ asset($order->customer->logo) }}" class="w-12 h-12 rounded-xl object-contain border border-slate-300 bg-white p-1" alt="">
                    @endif
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $order->customer?->name }}</h3>
                        <p class="text-slate-500 text-[11px]">{{ $order->customer?->contact_person ?? 'مسؤول المشتريات' }} | {{ $order->customer?->phone }}</p>
                    </div>
                </div>
                <div class="text-slate-600 pt-1">
                    <span class="text-slate-400">العنوان:</span> {{ $order->customer?->address ?? 'جمهورية مصر العربية' }}<br>
                    <span class="text-slate-400">السجل التجاري / البطاقة:</span> <span class="font-mono">{{ $order->customer?->tax_number ?? 'ساري' }}</span>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="space-y-2 pr-2">
                <span class="text-[11px] font-bold text-slate-400 block">بيانات الفاتورة والتسليم:</span>
                <div class="grid grid-cols-2 gap-2 font-mono">
                    <div>
                        <span class="text-slate-400 font-sans block text-[10px]">تاريخ الطلب:</span>
                        <strong class="text-slate-800">{{ $order->order_date?->format('Y-m-d') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans block text-[10px]">تاريخ التسليم المتفق عليه:</span>
                        <strong class="text-primary-700">{{ $order->confirmed_delivery_date?->format('Y-m-d') ?? 'قيد التحديد' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans block text-[10px]">مسؤول المبيعات:</span>
                        <strong class="font-sans text-slate-800">{{ $order->salesRep?->name ?? 'أبانوب مجدي' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans block text-[10px]">حالة الفاتورة:</span>
                        <span class="font-sans font-bold text-emerald-700">معتمدة للتسليم</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-3 px-3 text-center border-l border-slate-200">#</th>
                        <th class="py-3 px-4 border-l border-slate-200">الصنف / تشكيلة الكوڤير (مكونات الطباعة والغلاف)</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الكمية</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الوحدة</th>
                        <th class="py-3 px-4 text-left border-l border-slate-200 font-mono">سعر البيع التعاقدي</th>
                        <th class="py-3 px-4 text-left font-mono">القيمة الإجمالية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($order->items as $idx => $item)
                    <tr>
                        <td class="py-3 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 border-l border-slate-200 font-sans">
                            <span class="font-bold text-slate-900 block text-sm">{{ $item->product?->name ?? 'منتج كوڤير' }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $item->product?->code }}</span>
                            @if($item->custom_notes)
                                <span class="text-[10px] text-primary-700 block mt-0.5">{{ $item->custom_notes }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-bold text-slate-800 text-sm">
                            {{ number_format($item->quantity) }}
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-sans text-slate-600">
                            {{ $item->product?->unit ?? 'قطعة' }}
                        </td>
                        <td class="py-3 px-4 text-left border-l border-slate-200 text-slate-700">
                            {{ number_format($item->unit_price, 2) }} ج.م
                        </td>
                        <td class="py-3 px-4 text-left font-bold text-slate-900">
                            {{ number_format($item->total_price, 2) }} ج.م
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 font-sans">لا توجد بنود في هذا الطلب.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="max-w-md mr-auto bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs font-mono mb-8">
            <div class="flex justify-between items-center text-slate-600">
                <span class="font-sans">إجمالي قيمة البضاعة:</span>
                <span class="font-bold">{{ number_format($order->total_price, 2) }} ج.م</span>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span class="font-sans">ضريبة القيمة المضافة:</span>
                <span class="font-bold">مشمولة وفق الاتفاق</span>
            </div>
            <div class="flex justify-between items-center text-base font-bold text-slate-900 border-t border-slate-300 pt-2">
                <span class="font-sans">صافي القيمة المستحقة للسداد:</span>
                <span class="text-primary-800 text-lg">{{ number_format($order->total_price, 2) }} ج.م</span>
            </div>
        </div>

        <!-- Signatures & Acceptance -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300">
            <div class="grid grid-cols-3 gap-4 text-center text-xs">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">مسؤول المبيعات والتعاقد</span>
                    <strong class="block text-slate-800 mb-8">{{ $order->salesRep?->name ?? 'أبانوب مجدي' }}</strong>
                    <div class="border-b border-slate-400 w-36 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">اعتماد الحسابات والمالية</span>
                    <strong class="block text-slate-800 mb-8">عادل صادق</strong>
                    <div class="border-b border-slate-400 w-36 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">توقيع وختم العميل بالاستلام</span>
                    <strong class="block text-slate-800 mb-8">{{ $order->customer?->name }}</strong>
                    <div class="border-b border-slate-400 w-36 mx-auto"></div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-100 pt-3">
            شركة الفا ايجبت للتعبئة والتغليف - الرقم الضريبي 549-382-109 - شكراً لتعاملكم معنا © {{ date('Y') }}
        </div>
    </div>

</body>
</html>
