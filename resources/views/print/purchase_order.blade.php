<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أمر توريد وشراء {{ $purchaseOrder->po_number }} - الفا ايجبت</title>
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
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-teal-700 text-white font-bold text-sm shadow-md hover:bg-teal-800 transition">
            <i class="fa-solid fa-print"></i>
            <span>طباعة أمر التوريد (Print)</span>
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print-sheet">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-teal-900 text-teal-300 flex items-center justify-center font-black text-2xl shadow-md">
                    α
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-teal-900 font-bold">إدارة المشتريات وسلاسل التوريد (Procurement & Supply Chain)</p>
                    <p class="text-[11px] text-slate-500 mt-1">المنطقة الصناعية | س.ت: 182736 | ب.ض: 549-382-109</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">كود أمر التوريد</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-600 font-bold">{{ $purchaseOrder->po_number }}</span>
                </div>
            </div>
        </div>

        <div class="text-center my-6">
            <span class="inline-block px-8 py-2 rounded-xl text-lg font-black {{ $purchaseOrder->is_internal_factory ? 'bg-amber-50 text-amber-900 border border-amber-300' : 'bg-teal-50 text-teal-900 border border-teal-200' }}">
                {{ $purchaseOrder->is_internal_factory ? 'أمر توريد داخلي لمصنع مادة خام (+14% هامش تصنيع)' : 'أمر شراء وتوريد خامات خارجي (Purchase Order)' }}
            </span>
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-0.5">جهة التوريد:</span>
                <strong class="text-slate-800">
                    @if($purchaseOrder->is_internal_factory)
                        {{ $purchaseOrder->fromFactory?->name }} (مصنع داخلي)
                    @else
                        {{ $purchaseOrder->supplier?->name ?? 'مورد معتمد' }}
                    @endif
                </strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">تاريخ إصدار الأمر:</span>
                <strong class="font-mono text-slate-800">{{ $purchaseOrder->issue_date?->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">تاريخ التوريد للمخزن:</span>
                <strong class="font-mono text-slate-800">{{ $purchaseOrder->expected_delivery_date?->format('Y-m-d') ?? 'خلال المدة المتفق عليها' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">مسؤول المشتريات:</span>
                <strong class="text-slate-800">{{ $purchaseOrder->purchasingManager?->name ?? 'مؤمن عاطف' }}</strong>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-3 px-3 text-center border-l border-slate-200">#</th>
                        <th class="py-3 px-4 border-l border-slate-200">الخامة / الصنف المطلوب</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الكمية</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الوحدة</th>
                        <th class="py-3 px-4 text-left border-l border-slate-200 font-mono">سعر التكلفة المباشرة</th>
                        <th class="py-3 px-4 text-left font-mono">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($purchaseOrder->items as $idx => $it)
                    <tr>
                        <td class="py-3 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $it->rawMaterial?->name ?? 'مستلزم' }}
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-bold text-slate-800 text-sm">
                            {{ number_format($it->quantity) }}
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-sans text-slate-600">
                            {{ $it->rawMaterial?->unit ?? 'قطعة' }}
                        </td>
                        <td class="py-3 px-4 text-left border-l border-slate-200 text-slate-700">
                            {{ number_format($it->unit_cost, 4) }} ج.م
                        </td>
                        <td class="py-3 px-4 text-left font-bold text-slate-900">
                            {{ number_format($it->total_amount, 2) }} ج.م
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 font-sans">لا توجد بنود مسجلة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals & 14% Internal Calculation Breakdown -->
        <div class="max-w-md mr-auto bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs font-mono mb-8">
            <div class="flex justify-between items-center text-slate-600">
                <span class="font-sans">إجمالي تكلفة الخامات المباشرة:</span>
                <span class="font-bold">{{ number_format($purchaseOrder->subtotal, 2) }} ج.م</span>
            </div>
            @if($purchaseOrder->is_internal_factory)
            <div class="flex justify-between items-center text-amber-700 font-bold bg-amber-100/60 p-2 rounded-lg">
                <span class="font-sans">تكلفة تصنيع مضافة للمصنع الداخلي (+14%):</span>
                <span>+{{ number_format($purchaseOrder->internal_markup_amount, 2) }} ج.م</span>
            </div>
            @endif
            @if($purchaseOrder->tax_amount > 0)
            <div class="flex justify-between items-center text-slate-600">
                <span class="font-sans">ضرائب ومصروفات شحن:</span>
                <span class="font-bold">+{{ number_format($purchaseOrder->tax_amount, 2) }} ج.م</span>
            </div>
            @endif
            <div class="flex justify-between items-center text-sm font-bold text-slate-900 border-t border-slate-300 pt-2">
                <span class="font-sans">إجمالي أمر التوريد المقيد على المخزن:</span>
                <span class="text-teal-900 text-base">{{ number_format($purchaseOrder->total_amount, 2) }} ج.م</span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300">
            <div class="grid grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">مسؤول المشتريات</span>
                    <strong class="block text-slate-800 mb-8">{{ $purchaseOrder->purchasingManager?->name ?? 'مؤمن عاطف' }}</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">المورد / مدير المصنع الداخلي</span>
                    <strong class="block text-slate-800 mb-8">توقيع المورد</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">المراجعة المالية والحسابات</span>
                    <strong class="block text-slate-800 mb-8">عادل صادق</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">اعتماد المدير العام</span>
                    <strong class="block text-slate-800 mb-8">م. بولا الفيل</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-100 pt-3">
            Alfa ERP Industrial Suite - نظام إدارة التوريدات والمشتريات لشركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}
        </div>
    </div>

</body>
</html>
