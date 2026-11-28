<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محضر فحص واستلام {{ $inspection->inspection_number }} - الفا ايجبت</title>
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
            @page { size: A4 portrait; margin: 14mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 text-slate-800">

    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 font-bold text-sm shadow-sm hover:bg-slate-50 transition border border-slate-200">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع للنظام</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-700 text-white font-bold text-sm shadow-md hover:bg-blue-800 transition">
            <i class="fa-solid fa-print"></i>
            <span>طباعة محضر الفحص والاستلام (Print)</span>
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-lg border border-slate-200 print-sheet">

        <!-- Letterhead -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-5 mb-5">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol.png') }}" alt="Alfa Egypt" class="w-16 h-16 object-contain rounded-xl shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-red-600 font-bold">ALPHA EGYPT FOR MODERN PACKAGING INDUSTRIES</p>
                    <p class="text-[11px] text-slate-500 mt-1">إدارة المخازن المركزية ومراقبة جودة الاستلام</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">رقم المحضر</span>
                    <span class="block text-sm text-slate-900 font-black">{{ $inspection->inspection_number }}</span>
                </div>
            </div>
        </div>

        <div class="text-center my-5">
            <span class="inline-block px-6 py-2 rounded-xl text-base font-black border bg-amber-50 text-amber-900 border-amber-300">
                محضر فحص واستلام خامات / بضاعة بالمخزن المركزي
            </span>
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-5">
            <div>
                <span class="text-slate-400 block">تاريخ الفحص:</span>
                <strong class="font-mono text-slate-900">{{ $inspection->inspection_date?->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">الجهة الموردة / المصدر:</span>
                <strong class="text-slate-900">{{ $inspection->source_display_name }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">رقم فاتورة المورد / أمر الشراء:</span>
                <strong class="font-mono text-slate-900">{{ $inspection->supplier_invoice_number ?? $inspection->purchaseOrder?->po_number ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">المندوب / السائق المسلّم:</span>
                <strong class="text-slate-900">{{ $inspection->delivered_by ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">نتيجة الفحص الإجمالية:</span>
                <strong class="text-slate-900">{{ $inspection->status_arabic }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">إذن الإضافة المخزنية:</span>
                <strong class="font-mono text-slate-900">{{ $inspection->stockMovement?->voucher_number ?? 'غير مرحل' }}</strong>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-right text-xs border border-slate-300 mb-6">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                    <th class="py-2.5 px-2 text-center border-l border-slate-300">#</th>
                    <th class="py-2.5 px-3 border-l border-slate-300">اسم الصنف</th>
                    <th class="py-2.5 px-2 text-center border-l border-slate-300">الوحدة</th>
                    <th class="py-2.5 px-3 text-center border-l border-slate-300">الكمية الموردة</th>
                    <th class="py-2.5 px-3 text-center border-l border-slate-300">المقبول</th>
                    <th class="py-2.5 px-3 text-center border-l border-slate-300">المرفوض</th>
                    <th class="py-2.5 px-3 text-center border-l border-slate-300">حالة الفحص</th>
                    <th class="py-2.5 px-3">ملاحظات الفحص</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($inspection->items as $idx => $item)
                <tr>
                    <td class="py-2.5 px-2 text-center border-l border-slate-300 font-mono">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border-l border-slate-300 font-bold text-slate-900">{{ $item->item_name }}</td>
                    <td class="py-2.5 px-2 text-center border-l border-slate-300">{{ $item->unit }}</td>
                    <td class="py-2.5 px-3 text-center border-l border-slate-300 font-mono font-bold">{{ number_format($item->delivered_quantity, 2) }}</td>
                    <td class="py-2.5 px-3 text-center border-l border-slate-300 font-mono font-black text-emerald-800">{{ number_format($item->accepted_quantity, 2) }}</td>
                    <td class="py-2.5 px-3 text-center border-l border-slate-300 font-mono font-black text-rose-700">{{ number_format($item->rejected_quantity, 2) }}</td>
                    <td class="py-2.5 px-3 text-center border-l border-slate-300 font-bold">{{ $item->result_arabic }}</td>
                    <td class="py-2.5 px-3 text-slate-700">{{ $item->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($inspection->notes)
        <div class="mb-8 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700">
            <strong>ملاحظات لجنة الفحص:</strong> {{ $inspection->notes }}
        </div>
        @endif

        <!-- Signatures -->
        <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-300 text-center text-xs">
            <div class="space-y-8">
                <div class="font-bold text-slate-700">أمين المخزن (القائم بالفحص)</div>
                <div class="font-bold text-slate-900">{{ $inspection->inspector_name }}</div>
                <div class="border-b border-dashed border-slate-400 w-3/4 mx-auto"></div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">عضو لجنة الفحص / الجودة</div>
                <div class="font-bold text-slate-900">{{ $inspection->quality_member_name ?? '........................' }}</div>
                <div class="border-b border-dashed border-slate-400 w-3/4 mx-auto"></div>
            </div>
            <div class="space-y-8">
                <div class="font-bold text-slate-700">توقيع المندوب / المسلّم</div>
                <div class="font-bold text-slate-900">{{ $inspection->delivered_by ?? '........................' }}</div>
                <div class="border-b border-dashed border-slate-400 w-3/4 mx-auto"></div>
            </div>
        </div>

    </div>

</body>
</html>
