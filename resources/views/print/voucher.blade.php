<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إذن مخزني {{ $voucher->voucher_number }} - الفا ايجبت</title>
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

    <!-- Top Action Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 font-bold text-sm shadow-sm hover:bg-slate-50 transition border border-slate-200">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع للنظام</span>
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-700 text-white font-bold text-sm shadow-md hover:bg-blue-800 transition">
                <i class="fa-solid fa-print"></i>
                <span>طباعة الإذن المخزني (Print)</span>
            </button>
        </div>
    </div>

    <!-- Official Document Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print-sheet">
        
        <!-- Company Letterhead -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol.png') }}" alt="Alfa Egypt" class="w-16 h-16 object-contain rounded-xl shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-red-600 font-bold">ALPHA EGYPT FOR MODERN PACKAGING INDUSTRIES</p>
                    <p class="text-[11px] text-slate-500 mt-1">المنطقة الصناعية - قطاع الصناعات البلاستيكية والورقية | س.ت: 182736 | ب.ض: 549-382-109</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">باركود الإذن</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-600 font-bold">{{ $voucher->voucher_number }}</span>
                </div>
            </div>
        </div>

        <!-- Voucher Type Title -->
        @php
            $typeTitles = [
                'receipt' => ['title' => 'إذن إضافة مخزنية للمستودع', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300'],
                'issue' => ['title' => 'إذن صرف خامات ومستلزمات تشغيل', 'bg' => 'bg-blue-50 text-blue-800 border-blue-300'],
                'transfer' => ['title' => 'إذن تحويل بين المخازن ومواقع الإنتاج', 'bg' => 'bg-amber-50 text-amber-800 border-amber-300'],
                'adjustment' => ['title' => 'إذن تسوية وتسوية فروق جردية', 'bg' => 'bg-purple-50 text-purple-800 border-purple-300'],
            ];
            $currentType = $typeTitles[$voucher->type] ?? ['title' => 'إذن حركة مخزنية', 'bg' => 'bg-slate-100 text-slate-800 border-slate-300'];
        @endphp

        <div class="text-center my-6">
            <span class="inline-block px-6 py-2 rounded-xl text-base font-extrabold border {{ $currentType['bg'] }}">
                {{ $currentType['title'] }}
            </span>
        </div>

        <!-- Voucher Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-0.5">رقم الإذن المخزني:</span>
                <strong class="font-mono text-sm text-slate-800">{{ $voucher->voucher_number }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">تاريخ الحركة:</span>
                <strong class="font-mono text-slate-800">{{ $voucher->movement_date?->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">المخزن المركزي:</span>
                <strong class="text-slate-800">مستودع الخامات والمنتج التام</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">أمين المخزن المختص:</span>
                <strong class="text-slate-800">{{ $voucher->warehouseManager?->name ?? 'جورج وليم' }}</strong>
            </div>
        </div>

        @if($voucher->notes)
            <div class="mb-6 p-3 bg-slate-50/80 rounded-lg border border-slate-200 text-xs text-slate-700">
                <span class="font-bold text-slate-800">ملاحظات وبيان الصرف / الإضافة:</span> {{ $voucher->notes }}
            </div>
        @endif

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-3 px-3 text-center border-l border-slate-200">#</th>
                        <th class="py-3 px-4 border-l border-slate-200">الصنف / الخامة / المنتج التام</th>
                        <th class="py-3 px-3 text-center border-l border-slate-200">النوع</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الكمية المقيدة</th>
                        <th class="py-3 px-4 text-center border-l border-slate-200">الوحدة</th>
                        <th class="py-3 px-4 text-left border-l border-slate-200 font-mono">سعر الوحدة</th>
                        <th class="py-3 px-4 text-left font-mono">القيمة الإجمالية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @php $sumTotal = 0; @endphp
                    @forelse($voucher->items as $idx => $item)
                    @php 
                        $rowTotal = $item->quantity * $item->unit_cost;
                        $sumTotal += $rowTotal;
                    @endphp
                    <tr>
                        <td class="py-3 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $item->item_name }}
                        </td>
                        <td class="py-3 px-3 text-center border-l border-slate-200 font-sans">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px]">
                                {{ $item->item_type == 'raw_material' ? 'مادة خام' : ($item->item_type == 'product' ? 'منتج تام' : 'قطعة غيار') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-bold text-slate-800 text-sm">
                            {{ number_format($item->quantity) }}
                        </td>
                        <td class="py-3 px-4 text-center border-l border-slate-200 font-sans text-slate-600">
                            {{ $item->unit }}
                        </td>
                        <td class="py-3 px-4 text-left border-l border-slate-200 text-slate-700">
                            {{ number_format($item->unit_cost, 2) }} ج.م
                        </td>
                        <td class="py-3 px-4 text-left font-bold text-slate-900">
                            {{ number_format($rowTotal, 2) }} ج.م
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400 font-sans">لا توجد بنود مدرجة في هذا الإذن.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-slate-50 font-bold text-sm border-t-2 border-slate-300">
                        <td colspan="6" class="py-3 px-4 text-slate-800 font-sans text-left border-l border-slate-200">
                            إجمالي القيمة المخزنية للإذن:
                        </td>
                        <td class="py-3 px-4 text-left text-blue-900">
                            {{ number_format($sumTotal, 2) }} ج.م
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Official Signatures Block -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300">
            <div class="grid grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">أمين المخزن المركزي</span>
                    <strong class="block text-slate-800 mb-8">{{ $voucher->warehouseManager?->name ?? 'جورج وليم' }}</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                    <span class="text-[10px] text-slate-400 mt-1 block">التوقيع والتاريخ</span>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">المستلم / مدير التشغيل</span>
                    <strong class="block text-slate-800 mb-8">جرجس أنور</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                    <span class="text-[10px] text-slate-400 mt-1 block">التوقيع والتاريخ</span>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">المراجعة والتدقيق المالي</span>
                    <strong class="block text-slate-800 mb-8">عادل صادق</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                    <span class="text-[10px] text-slate-400 mt-1 block">التوقيع والتاريخ</span>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">اعتماد الإدارة العليا</span>
                    <strong class="block text-slate-800 mb-8">م. بولا الفيل</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                    <span class="text-[10px] text-slate-400 mt-1 block">خاتم الشركة والاعتماد</span>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-100 pt-3">
            تم استخراج وطباعة هذا المستند إلكترونياً عبر نظام Alfa ERP - جميع الحقوق محفوظة لشركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}
        </div>
    </div>

</body>
</html>
