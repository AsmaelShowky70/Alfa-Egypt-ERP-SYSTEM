<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محضر الجرد اليومي {{ $dailyCheck->check_number }} - الفا ايجبت</title>
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
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 text-slate-800">

    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 font-bold text-sm shadow-sm hover:bg-slate-50 transition border border-slate-200">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-700 text-white font-bold text-sm shadow-md hover:bg-blue-800 transition">
            <i class="fa-solid fa-print"></i>
            <span>طباعة محضر الجرد المخزني (Print)</span>
        </button>
    </div>

    <div class="max-w-5xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-lg border border-slate-200 print-sheet">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-950 text-blue-300 flex items-center justify-center font-black text-2xl shadow-md">
                    α
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900">شركة الفا ايجبت للتعبئة والتغليف</h1>
                    <p class="text-xs text-blue-900 font-bold">إدارة المخازن المركزية - لجنة الجرد الدوري والمطابقة الفعلية</p>
                    <p class="text-[11px] text-slate-500 mt-1">جرد يومي للمواد الخام والمنتج التام وقطع الغيار | س.ت: 182736</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-2 bg-slate-50 border border-slate-300 rounded-lg text-center">
                    <span class="block text-[10px] text-slate-500 font-sans">كود محضر الجرد</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-600 font-bold">{{ $dailyCheck->check_number }}</span>
                </div>
            </div>
        </div>

        <div class="text-center my-6">
            <span class="inline-block px-8 py-2 rounded-xl text-lg font-black bg-blue-50 text-blue-900 border border-blue-200">
                محضر مطابقة الجرد الفعلي للمخزون (Daily Stock Count)
            </span>
        </div>

        <!-- Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-0.5">تاريخ الجرد:</span>
                <strong class="font-mono text-slate-800">{{ $dailyCheck->check_date?->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">قطاع الجرد:</span>
                <strong class="text-slate-800">
                    {{ $dailyCheck->category == 'raw_materials' ? 'المواد الخام ومستلزمات الإنتاج' : ($dailyCheck->category == 'finished_goods' ? 'المنتج التام والكوڤير' : 'قطع الغيار والعدد') }}
                </strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">رئيس لجنة الجرد (أمين المخزن):</span>
                <strong class="text-slate-800">{{ $dailyCheck->warehouseManager?->name ?? 'جورج وليم' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">حالة المطابقة:</span>
                <strong class="text-slate-800">{{ $dailyCheck->status == 'approved' ? 'معتمد ومطابق' : 'قيد المراجعة' }}</strong>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <th class="py-3 px-3 text-center border-l border-slate-200">#</th>
                        <th class="py-3 px-4 border-l border-slate-200">الصنف / المادة المفحوصة</th>
                        <th class="py-3 px-3 text-center border-l border-slate-200 font-mono">الرصيد الدفتري (السيستم)</th>
                        <th class="py-3 px-3 text-center border-l border-slate-200 font-mono">الرصيد الفعلي (المعدود)</th>
                        <th class="py-3 px-3 text-center border-l border-slate-200 font-mono">فرق الكمية</th>
                        <th class="py-3 px-3 text-left border-l border-slate-200 font-mono">سعر الوحدة</th>
                        <th class="py-3 px-3 text-left border-l border-slate-200 font-mono">قيمة الفرق (ج.م)</th>
                        <th class="py-3 px-4">ملاحظات وأسباب الفروق الجردية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($dailyCheck->items as $idx => $it)
                    @php
                        $hasDiff = $it->difference != 0;
                    @endphp
                    <tr class="{{ $hasDiff ? ($it->difference < 0 ? 'bg-rose-50/50' : 'bg-amber-50/50') : '' }}">
                        <td class="py-3 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $it->item_name }}
                        </td>
                        <td class="py-3 px-3 text-center border-l border-slate-200 text-slate-700">
                            {{ number_format($it->system_quantity) }}
                        </td>
                        <td class="py-3 px-3 text-center border-l border-slate-200 font-bold text-slate-900">
                            {{ number_format($it->actual_quantity) }}
                        </td>
                        <td class="py-3 px-3 text-center border-l border-slate-200 font-bold">
                            @if($it->difference == 0)
                                <span class="text-emerald-600 font-sans text-xs">مطابق 0</span>
                            @elseif($it->difference < 0)
                                <span class="text-rose-600">{{ number_format($it->difference) }} (عجز)</span>
                            @else
                                <span class="text-amber-600">+{{ number_format($it->difference) }} (فائض)</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-left border-l border-slate-200 text-slate-700">
                            {{ number_format($it->unit_cost, 4) }} ج.م
                        </td>
                        <td class="py-3 px-3 text-left border-l border-slate-200 font-bold">
                            @if($it->variance_value < 0)
                                <span class="text-rose-600">{{ number_format($it->variance_value, 2) }} ج.م</span>
                            @elseif($it->variance_value > 0)
                                <span class="text-amber-600">+{{ number_format($it->variance_value, 2) }} ج.م</span>
                            @else
                                <span class="text-slate-400">0.00 ج.م</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-sans text-[11px] text-slate-600">
                            {{ $it->notes ?? 'لا توجد ملاحظات' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-400 font-sans">لا توجد بنود مفحوصة في هذا المحضر.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="mt-12 pt-8 border-t-2 border-dashed border-slate-300">
            <div class="grid grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">أمين المخزن المختص</span>
                    <strong class="block text-slate-800 mb-8">{{ $dailyCheck->warehouseManager?->name ?? 'جورج وليم' }}</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">عضو لجنة الجرد</span>
                    <strong class="block text-slate-800 mb-8">مسؤول التدقيق والمطابقة</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">إدارة التكاليف والحسابات</span>
                    <strong class="block text-slate-800 mb-8">عادل صادق</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">اعتماد الإدارة العليا</span>
                    <strong class="block text-slate-800 mb-8">م. بولا الفيل</strong>
                    <div class="border-b border-slate-400 w-32 mx-auto"></div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-100 pt-3">
            نظام المطابقة الرقابية والجرد المستمر - شركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}
        </div>
    </div>

</body>
</html>
