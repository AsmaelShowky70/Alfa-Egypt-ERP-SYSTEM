<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقرير المؤرشف {{ $archivedReport->report_number }} - الفا ايجبت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-6 text-slate-800">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="max-w-6xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.reports.archived') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                <i class="fa-solid fa-arrow-right"></i>
                <span>رجوع للأرشيف المعتمد</span>
            </a>
            <span class="text-xs text-slate-400">|</span>
            <span class="text-xs font-bold text-slate-600">
                تقرير رسمي مجمد ومؤرشف رقم: <span class="font-mono text-amber-700">{{ $archivedReport->report_number }}</span>
            </span>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Share via WhatsApp -->
            <button type="button" onclick="shareViaWhatsApp()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md hover:bg-emerald-700 transition">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>مشاركة عبر واتساب</span>
            </button>

            <!-- Copy Link -->
            <button type="button" onclick="copyReportLink()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition border border-slate-200">
                <i class="fa-solid fa-link"></i>
                <span id="copyBtnText">نسخ الرابط المعتمد</span>
            </button>

            <!-- Print / Save PDF -->
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md hover:bg-slate-800 transition">
                <i class="fa-solid fa-print"></i>
                <span>طباعة الوثيقة الرسمية / PDF</span>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="max-w-6xl mx-auto mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Official Document Sheet -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-200 print-sheet">
        
        <!-- Official Letterhead -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/alpha_symbol.png') }}" alt="Alfa Egypt" class="w-16 h-16 object-contain rounded-xl shadow-sm border border-slate-200 p-1">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">شركة الفا ايجبت للتعبئة والتغليف الحديث</h1>
                    <div class="flex items-center gap-2 text-xs font-bold text-red-600 mt-0.5">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>إدارة المخازن المركزية وحركة الأصناف</span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-600">سجل تجاري: 182736 - بطاقة ضريبية: 492-817-302</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">مصانع التشغيل: أوتوباك (AutoPack) - بلينز باك (PlainsPack) - مصطفى قطين (Mostafa Qattin)</p>
                </div>
            </div>
            <div class="text-left font-mono">
                <div class="inline-block p-3 bg-slate-50 border border-slate-300 rounded-xl text-center">
                    <span class="block text-[10px] text-slate-500 font-sans font-bold">كود الأرشفة المعتمد</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-amber-700 font-bold mt-0.5">{{ $archivedReport->report_number }}</span>
                </div>
            </div>
        </div>

        <!-- Report Title Banner -->
        <div class="text-center my-6">
            <span class="inline-block px-8 py-2.5 rounded-2xl text-lg font-black bg-slate-900 text-white shadow-sm border border-slate-800">
                {{ $archivedReport->title }}
            </span>
            <div class="text-xs text-slate-500 font-bold mt-2">
                وثيقة مخزنية مؤرشفة رسمياً - معتمدة وموثقة التوقيعات إلكترونياً
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-1">الفترة الزمنية للتقرير:</span>
                <strong class="font-mono text-slate-800 text-sm">
                    من {{ $archivedReport->from_date?->format('Y-m-d') ?? '-' }} إلى {{ $archivedReport->to_date?->format('Y-m-d') ?? '-' }}
                </strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">الجهة الموجه إليها التقرير:</span>
                <strong class="text-slate-800 text-sm">{{ $archivedReport->party_name ?? 'كافة المصانع والجهات' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">أمين المخزن المختص:</span>
                <strong class="text-slate-800 text-sm">{{ $archivedReport->warehouseManager?->name ?? 'جورج وليم' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">تاريخ ووقت الأرشفة:</span>
                <strong class="font-mono text-slate-800 text-sm">{{ $archivedReport->created_at->format('Y-m-d H:i') }}</strong>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-3 bg-slate-100 rounded-xl border border-slate-200 text-center">
                <span class="text-[11px] text-slate-500 block font-bold">إجمالي الكميات المسلمة / المستلمة</span>
                <span class="text-xl font-black font-mono text-slate-900">{{ number_format($archivedReport->total_quantity, 2) }}</span>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <span class="text-[11px] text-emerald-700 block font-bold">إجمالي القيمة التقديرية</span>
                <span class="text-xl font-black font-mono text-emerald-800">{{ number_format($archivedReport->total_value, 2) }} ج.م</span>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-center">
                <span class="text-[11px] text-blue-700 block font-bold">عدد السجلات والبنود المؤرشفة</span>
                <span class="text-xl font-black font-mono text-blue-900">{{ number_format($archivedReport->items_count) }}</span>
            </div>
        </div>

        <!-- Snapshot Items Table -->
        @php
            $payloadItems = $archivedReport->data_payload['items'] ?? [];
        @endphp
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-300">
                <thead>
                    <tr class="bg-slate-200 text-slate-800 font-bold border-b border-slate-300">
                        <th class="py-2.5 px-3 text-center border-l border-slate-300">#</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">تاريخ السند</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">رقم الإذن</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">الصنف / الخامة</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-300 font-mono">الكمية</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-300">الوحدة</th>
                        <th class="py-2.5 px-3 text-left border-l border-slate-300 font-mono">سعر الوحدة</th>
                        <th class="py-2.5 px-3 text-left border-l border-slate-300 font-mono">القيمة (ج.م)</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">الجهة المسلمة / المستلمة</th>
                        <th class="py-2.5 px-3">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($payloadItems as $idx => $it)
                    <tr class="text-slate-700 hover:bg-slate-50 transition">
                        <td class="py-2 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3 border-l border-slate-200">{{ $it['voucher_date'] ?? '-' }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-bold text-slate-900">{{ $it['voucher_number'] ?? '-' }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-sans font-bold text-slate-900">
                            {{ $it['item_name'] ?? '-' }}
                        </td>
                        <td class="py-2 px-3 text-center border-l border-slate-200 font-bold text-slate-900">
                            {{ number_format((float)($it['quantity'] ?? 0), 2) }}
                        </td>
                        <td class="py-2 px-3 text-center border-l border-slate-200 font-sans text-slate-600">{{ $it['unit'] ?? 'قطعة' }}</td>
                        <td class="py-2 px-3 text-left border-l border-slate-200">{{ number_format((float)($it['unit_cost'] ?? 0), 2) }}</td>
                        <td class="py-2 px-3 text-left border-l border-slate-200 font-bold text-slate-900">{{ number_format((float)($it['total_cost'] ?? 0), 2) }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $it['party_name'] ?? '-' }}
                        </td>
                        <td class="py-2 px-3 font-sans text-[11px] text-slate-500">
                            {{ $it['notes'] ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center font-sans text-slate-400">لا توجد بنود في اللقطة المحفوظة.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($payloadItems) > 0)
                <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300 font-mono">
                    <tr>
                        <td colspan="4" class="py-3 px-3 text-left font-sans font-black">إجمالي كافة البنود المؤرشفة:</td>
                        <td class="py-3 px-3 text-center font-black text-amber-700">{{ number_format($archivedReport->total_quantity, 2) }}</td>
                        <td></td>
                        <td></td>
                        <td class="py-3 px-3 text-left font-black text-emerald-700">{{ number_format($archivedReport->total_value, 2) }} ج.م</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Saved Digital Signatures Display -->
        <div class="mt-8 pt-6 border-t-2 border-dashed border-slate-400">
            <div class="text-center font-bold text-xs text-slate-600 mb-4">
                شهادة الاعتماد والتوقيعات الرقمية الموثقة رسمياً
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Signature 1: Warehouse Manager -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-300 text-center">
                    <span class="font-black text-slate-800 text-xs block mb-1">
                        توقيع أمين / مدير المخازن المختص
                    </span>
                    <div class="text-[11px] text-slate-600 mb-2">
                        الاسم: <strong class="text-slate-900">{{ $archivedReport->warehouseManager?->name ?? 'جورج وليم' }}</strong>
                    </div>

                    <div class="h-28 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-2 shadow-inner">
                        @if($archivedReport->warehouse_manager_signature)
                            <img src="{{ $archivedReport->warehouse_manager_signature }}" alt="توقيع أمين المخزن" class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-center text-slate-400">
                                <span class="font-bold text-xs text-slate-700 block">{{ $archivedReport->warehouseManager?->name ?? 'أمين المخزن' }}</span>
                                <span class="text-[10px]">معتمد رسمياً بالنظام</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Signature 2: Recipient Party -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-300 text-center">
                    <span class="font-black text-slate-800 text-xs block mb-1">
                        توقيع الجهة الموجه إليها التقرير (المستلم)
                    </span>
                    <div class="text-[11px] text-slate-600 mb-2">
                        الاسم: <strong class="text-slate-900">{{ $archivedReport->recipient_name ?? 'مسؤول الاستلام' }}</strong>
                        @if($archivedReport->recipient_title)
                            | الصفة: <strong class="text-slate-900">{{ $archivedReport->recipient_title }}</strong>
                        @endif
                        @if($archivedReport->recipient_phone)
                            | هاتف: <strong class="text-slate-900 font-mono">{{ $archivedReport->recipient_phone }}</strong>
                        @endif
                    </div>

                    <div class="h-28 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-2 shadow-inner">
                        @if($archivedReport->recipient_signature)
                            <img src="{{ $archivedReport->recipient_signature }}" alt="توقيع المستلم" class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-center text-slate-400">
                                <span class="font-bold text-xs text-slate-700 block">{{ $archivedReport->recipient_name ?? 'الجهة المستلمة' }}</span>
                                <span class="text-[10px]">معتمد ومطابق</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($archivedReport->notes)
            <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                <strong class="text-amber-900">ملاحظات الأرشفة:</strong>
                <span class="text-slate-700">{{ $archivedReport->notes }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-200 pt-3 flex items-center justify-between">
            <span>منظومة الأرشيف الرقمي المعتمد - شركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}</span>
            <span class="font-mono">وثيقة رقم: {{ $archivedReport->report_number }}</span>
        </div>
    </div>

    <script>
        // Copy link
        function copyReportLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btn = document.getElementById('copyBtnText');
                btn.textContent = 'تم نسخ الرابط!';
                setTimeout(() => { btn.textContent = 'نسخ الرابط المعتمد'; }, 2000);
            });
        }

        // WhatsApp Share
        function shareViaWhatsApp() {
            const recipientPhone = "{{ $archivedReport->recipient_phone }}";
            const reportNumber = "{{ $archivedReport->report_number }}";
            const reportTitle = "{{ $archivedReport->title }}";
            const partyName = "{{ $archivedReport->party_name ?? 'الجهة المعتمدة' }}";
            const totalQty = "{{ number_format($archivedReport->total_quantity, 2) }}";
            const totalVal = "{{ number_format($archivedReport->total_value, 2) }} ج.م";
            const currentUrl = window.location.href;

            const message = `*شركة الفا ايجبت للتعبئة والتغليف* 📦\n` +
                `*تقرير مخزني معتمد ومؤرشف رسمياً*\n` +
                `📋 *رقم التقرير:* ${reportNumber}\n` +
                `📌 *الموضوع:* ${reportTitle}\n` +
                `🏢 *الجهة:* ${partyName}\n` +
                `🔢 *إجمالي الكمية:* ${totalQty}\n` +
                `💰 *إجمالي القيمة:* ${totalVal}\n` +
                `🔗 *رابط الوثيقة الرسمية والتوقيعات:*\n${currentUrl}\n\n` +
                `_إدارة المخازن المركزية - الفا ايجبت_`;

            let waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`;
            if (recipientPhone) {
                let cleanPhone = recipientPhone.replace(/[^0-9]/g, '');
                if (cleanPhone.startsWith('01')) {
                    cleanPhone = '20' + cleanPhone.substring(1);
                }
                waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(message)}`;
            }

            window.open(waUrl, '_blank');
        }

        @if(request()->has('print'))
        window.addEventListener('DOMContentLoaded', () => {
            window.print();
        });
        @endif
    </script>
</body>
</html>
