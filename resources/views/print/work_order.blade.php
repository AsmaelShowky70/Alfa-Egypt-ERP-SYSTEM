<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أمر تشغيل إنتاجي {{ $productionOrder->order_number }} - الفا ايجبت</title>
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
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-indigo-700 text-white font-bold text-sm shadow-md hover:bg-indigo-800 transition">
            <i class="fa-solid fa-print"></i>
            <span>طباعة أمر التشغيل المصنعي (Print)</span>
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print-sheet">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol.png') }}" alt="Alfa Egypt" class="w-16 h-16 object-contain rounded-xl shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-red-600 font-bold">إدارة التشغيل والإنتاج الصناعي (Manufacturing Operations)</p>
                    <p class="text-[11px] text-slate-500 mt-1">بطاقة تشغيل خط الإنتاج ومواصفات خامات الكوڤير القياسية</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">كود أمر التشغيل</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-600 font-bold">{{ $productionOrder->order_number }}</span>
                </div>
            </div>
        </div>

        <div class="text-center my-6">
            <span class="inline-block px-8 py-2 rounded-xl text-lg font-black bg-indigo-50 text-indigo-900 border border-indigo-200">
                أمر تشغيل مصنعي (Work Order)
            </span>
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-0.5">خط الإنتاج / المصنع:</span>
                <strong class="text-slate-800">{{ $productionOrder->factory?->name ?? 'خط الكوڤير' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">الماكينة المخصصة:</span>
                <strong class="text-slate-800 font-mono">{{ $productionOrder->machine?->name ?? 'MCH-01' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">سرعة السير القياسية:</span>
                <strong class="text-slate-800 font-mono">{{ $productionOrder->machine?->belt_speed_m_min ?? 25.0 }} م/دقيقة</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">مدير التشغيل:</span>
                <strong class="text-slate-800">{{ $productionOrder->operationsManager?->name ?? 'جرجس أنور' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">العميل المستهدف:</span>
                <strong class="text-indigo-900 font-bold">{{ $productionOrder->salesOrder?->customer?->name ?? 'طلب مسبق للمخزن' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">أمر المبيعات المرتبط:</span>
                <strong class="font-mono text-slate-800">{{ $productionOrder->salesOrder?->order_number ?? 'تخزين مركزي' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">تاريخ بدء التشغيل:</span>
                <strong class="font-mono text-slate-800">{{ $productionOrder->start_date?->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">موعد التسليم للمخزن:</span>
                <strong class="font-mono text-slate-800">{{ $productionOrder->due_date?->format('Y-m-d') }}</strong>
            </div>
        </div>

        <!-- Target Product Box -->
        <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-center justify-between mb-6">
            <div>
                <span class="text-xs text-indigo-700 font-bold block">المنتج المطلوب إنتاجه:</span>
                <h3 class="text-lg font-black text-slate-800">{{ $productionOrder->product?->name }}</h3>
                <span class="text-xs font-mono text-slate-500">{{ $productionOrder->product?->code }}</span>
            </div>
            <div class="text-left font-mono">
                <span class="text-xs text-slate-500 font-sans block">الكمية المستهدفة</span>
                <span class="text-2xl font-black text-indigo-900">{{ number_format($productionOrder->quantity_to_produce) }}</span>
                <span class="text-xs font-sans text-slate-600">{{ $productionOrder->product?->unit ?? 'قطعة' }}</span>
            </div>
        </div>

        <!-- BOM Components Required -->
        <div class="mb-8">
            <h4 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-600"></i>
                <span>مستلزمات التشغيل والخامات المطلوبة من المخزن (BOM Matrix)</span>
            </h4>
            <table class="w-full text-right text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-2.5 px-3 border-l border-slate-200">مكون الخامة</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-200">المعيار للوحدة الواحدة</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-200 font-mono">إجمالي المطلوب للتشغيلة</th>
                        <th class="py-2.5 px-3 text-center">الوحدة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($productionOrder->product?->boms ?? [] as $bom)
                    @php
                        $totalReq = $bom->quantity * $productionOrder->quantity_to_produce;
                    @endphp
                    <tr>
                        <td class="py-2.5 px-3 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $bom->rawMaterial?->name ?? 'مادة خام' }}
                        </td>
                        <td class="py-2.5 px-3 text-center border-l border-slate-200 text-slate-700">
                            {{ $bom->quantity }}
                        </td>
                        <td class="py-2.5 px-3 text-center border-l border-slate-200 font-bold text-indigo-900 text-sm">
                            {{ number_format($totalReq, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-sans text-slate-600">
                            {{ $bom->rawMaterial?->unit ?? 'وحدة' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-400 font-sans">لا توجد بطاقة مكونات مسجلة لهذا المنتج.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300">
            <div class="grid grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">مدير التشغيل المصنعي</span>
                    <strong class="block text-slate-800 mb-8">{{ $productionOrder->operationsManager?->name ?? 'جرجس أنور' }}</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">مشغل الماكينة المسندة</span>
                    <strong class="block text-slate-800 mb-8">فني التشغيل المسؤول</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">مراقبة الجودة (QC)</span>
                    <strong class="block text-slate-800 mb-8">مهندس الجودة</strong>
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
            Alfa ERP Industrial Suite - نظام إدارة خطوط الإنتاج والكوڤير لشركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}
        </div>
    </div>

</body>
</html>
