<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }} - شركة الفا ايجبت</title>
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
            .signature-box { border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-6 text-slate-800">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="max-w-6xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <a href="{{ route('warehouse.reports', request()->all()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                <i class="fa-solid fa-arrow-right"></i>
                <span>رجوع لجدول التقارير</span>
            </a>
            <span class="text-xs text-slate-400">|</span>
            <span class="text-xs font-bold text-slate-600">
                جاهز للطباعة والحفظ PDF وتوثيق التوقيعات المزدوجة
            </span>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Share via WhatsApp -->
            <button type="button" onclick="shareViaWhatsApp()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md hover:bg-emerald-700 transition">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>مشاركة عبر واتساب</span>
            </button>

            <!-- Print / Save PDF -->
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md hover:bg-slate-800 transition">
                <i class="fa-solid fa-print"></i>
                <span>طباعة التقرير / حفظ PDF</span>
            </button>

            <!-- Save & Archive with Signatures -->
            <button type="button" onclick="submitArchivedReport()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md hover:bg-amber-700 transition">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>اعتماد وأرشفة التقرير بالسيستم</span>
            </button>
        </div>
    </div>

    <!-- Official Report Document Container -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-200 print-sheet">
        
        <!-- Official Letterhead (Alfa Egypt Branding) -->
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
                    <span class="block text-[10px] text-slate-500 font-sans font-bold">كود المتابعة والتوثيق</span>
                    <span class="text-sm font-bold tracking-widest text-slate-800">||| |||| || |||||</span>
                    <span class="block text-[11px] text-slate-800 font-bold mt-0.5">WH-REP-{{ date('Ymd-His') }}</span>
                </div>
            </div>
        </div>

        <!-- Report Title Banner -->
        <div class="text-center my-6">
            <span class="inline-block px-8 py-2.5 rounded-2xl text-lg font-black bg-slate-900 text-white shadow-sm border border-slate-800">
                {{ $meta['title'] }}
            </span>
            <div class="text-xs text-slate-500 font-bold mt-2">
                تقرير رسمي معتمد بحركة وتداول الأصناف والمواد الخام ومستلزمات الكوڤير
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block mb-1">الفترة الزمنية للتقرير:</span>
                <strong class="font-mono text-slate-800 text-sm">من {{ $fromDate }} إلى {{ $toDate }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">الجهة الموجه إليها التقرير:</span>
                <strong class="text-slate-800 text-sm">{{ $meta['party_name'] ?? 'كافة المصانع والجهات المعتمدة' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">الصنف المطبق عليه الفلترة:</span>
                <strong class="text-slate-800 text-sm">{{ $meta['item_name'] ?? 'كافة الأصناف المسجلة' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">أمين المخزن المحرر للتقرير:</span>
                <strong class="text-slate-800 text-sm">{{ Auth::user()->name ?? 'جورج وليم - أمين المخزن' }}</strong>
            </div>
        </div>

        <!-- KPI Summary Cards for Print -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-3 bg-slate-100 rounded-xl border border-slate-200 text-center">
                <span class="text-[11px] text-slate-500 block font-bold">إجمالي الكميات المسلمة / المستلمة</span>
                <span class="text-xl font-black font-mono text-slate-900">{{ number_format($totalQuantity, 2) }}</span>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <span class="text-[11px] text-emerald-700 block font-bold">إجمالي القيمة التقديرية</span>
                <span class="text-xl font-black font-mono text-emerald-800">{{ number_format($totalValue, 2) }} ج.م</span>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-center">
                <span class="text-[11px] text-blue-700 block font-bold">عدد أذونات وسجلات الحركة</span>
                <span class="text-xl font-black font-mono text-blue-900">{{ number_format($itemsCount) }}</span>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-right text-xs border border-slate-300">
                <thead>
                    <tr class="bg-slate-200 text-slate-800 font-bold border-b border-slate-300">
                        <th class="py-2.5 px-3 text-center border-l border-slate-300">#</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">تاريخ السند</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">رقم الإذن</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">نوع الحركة</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">الصنف / الخامة</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-300 font-mono">الكمية</th>
                        <th class="py-2.5 px-3 text-center border-l border-slate-300">الوحدة</th>
                        <th class="py-2.5 px-3 text-left border-l border-slate-300 font-mono">سعر الوحدة</th>
                        <th class="py-2.5 px-3 text-left border-l border-slate-300 font-mono">القيمة (ج.م)</th>
                        <th class="py-2.5 px-3 border-l border-slate-300">الجهة (المصنع / المورد / العميل)</th>
                        <th class="py-2.5 px-3">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-mono">
                    @forelse($items as $idx => $mItem)
                    <tr class="text-slate-700 hover:bg-slate-50 transition">
                        <td class="py-2 px-3 text-center border-l border-slate-200 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3 border-l border-slate-200">{{ $mItem->stockMovement?->voucher_date?->format('Y-m-d') }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-bold text-slate-900">{{ $mItem->stockMovement?->voucher_number }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-sans text-[11px] font-bold">
                            {{ $mItem->stockMovement?->type_arabic }}
                        </td>
                        <td class="py-2 px-3 border-l border-slate-200 font-sans font-bold text-slate-900">
                            {{ $mItem->item_name }}
                        </td>
                        <td class="py-2 px-3 text-center border-l border-slate-200 font-bold text-slate-900">
                            {{ number_format($mItem->quantity, 2) }}
                        </td>
                        <td class="py-2 px-3 text-center border-l border-slate-200 font-sans text-slate-600">{{ $mItem->unit }}</td>
                        <td class="py-2 px-3 text-left border-l border-slate-200">{{ number_format($mItem->unit_cost, 2) }}</td>
                        <td class="py-2 px-3 text-left border-l border-slate-200 font-bold text-slate-900">{{ number_format($mItem->total_cost, 2) }}</td>
                        <td class="py-2 px-3 border-l border-slate-200 font-sans font-bold text-slate-800">
                            {{ $mItem->stockMovement?->party_display_name }}
                        </td>
                        <td class="py-2 px-3 font-sans text-[11px] text-slate-500">
                            {{ $mItem->notes ?? $mItem->stockMovement?->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-6 text-center font-sans text-slate-400">لا توجد حركات مسجلة لهذه المعايير.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($items->count() > 0)
                <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300 font-mono">
                    <tr>
                        <td colspan="5" class="py-3 px-3 text-left font-sans font-black">إجمالي كافة البنود:</td>
                        <td class="py-3 px-3 text-center font-black text-amber-700">{{ number_format($totalQuantity, 2) }}</td>
                        <td></td>
                        <td></td>
                        <td class="py-3 px-3 text-left font-black text-emerald-700">{{ number_format($totalValue, 2) }} ج.م</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Dual Signature Section (Interactive Touchpad Canvas + Paper Ready) -->
        <div class="mt-8 pt-6 border-t-2 border-dashed border-slate-400">
            <div class="text-center font-bold text-xs text-slate-600 mb-4">
                إقرار المطابقة والاعتماد الرسمي (توقيع الطرفين لتثبيت صحة الاستلام والصرف والأرشفة الورقية والإلكترونية)
            </div>

            <form id="archiveForm" action="{{ route('warehouse.reports.archive') }}" method="POST">
                @csrf
                <!-- Hidden report parameters -->
                <input type="hidden" name="report_type" value="{{ $meta['report_type'] }}">
                <input type="hidden" name="title" value="{{ $meta['title'] }}">
                <input type="hidden" name="from_date" value="{{ $fromDate }}">
                <input type="hidden" name="to_date" value="{{ $toDate }}">
                <input type="hidden" name="item_type" value="{{ $meta['item_type'] }}">
                <input type="hidden" name="item_id" value="{{ $meta['item_id'] }}">
                <input type="hidden" name="item_name" value="{{ $meta['item_name'] }}">
                <input type="hidden" name="party_type" value="{{ $meta['party_type'] }}">
                <input type="hidden" name="party_id" value="{{ $meta['party_id'] }}">
                <input type="hidden" name="party_name" value="{{ $meta['party_name'] }}">
                <input type="hidden" name="total_quantity" value="{{ $totalQuantity }}">
                <input type="hidden" name="total_value" value="{{ $totalValue }}">
                <input type="hidden" name="warehouse_manager_signature" id="wm_signature_input">
                <input type="hidden" name="recipient_signature" id="rc_signature_input">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Party 1: Warehouse Keeper Signature -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-300 signature-box">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-slate-800 text-xs">
                                <i class="fa-solid fa-signature text-amber-600 ml-1"></i>
                                توقيع أمين / مدير المخازن المختص:
                            </span>
                            <button type="button" onclick="clearCanvas('canvas_wm')" class="text-[10px] text-red-500 hover:underline no-print font-bold">
                                مسح التوقيع وإعادة الرسم
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-600 mb-2">
                            الاسم: <strong class="text-slate-900">{{ Auth::user()->name ?? 'جورج وليم' }}</strong>
                            | التاريخ: <strong class="text-slate-900 font-mono">{{ date('Y-m-d') }}</strong>
                        </div>

                        <!-- Canvas Pad for Warehouse Manager -->
                        <div class="relative bg-white rounded-xl border border-slate-300 overflow-hidden shadow-inner h-28">
                            <canvas id="canvas_wm" width="500" height="112" class="w-full h-full cursor-crosshair touch-none"></canvas>
                            <span class="absolute bottom-1 right-2 text-[9px] text-slate-300 pointer-events-none select-none">
                                وقع باللمس أو القلم أو الماوس هنا
                            </span>
                        </div>
                    </div>

                    <!-- Party 2: Recipient / Delivered-to Party Signature -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-300 signature-box">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-slate-800 text-xs">
                                <i class="fa-solid fa-user-check text-blue-600 ml-1"></i>
                                توقيع الجهة الموجه إليها التقرير (المستلم):
                            </span>
                            <button type="button" onclick="clearCanvas('canvas_rc')" class="text-[10px] text-red-500 hover:underline no-print font-bold">
                                مسح التوقيع وإعادة الرسم
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <input type="text" name="recipient_name" id="recipient_name" placeholder="اسم المستلم *" required
                                   value="{{ $meta['party_name'] ? 'مسؤول ' . $meta['party_name'] : '' }}"
                                   class="text-xs p-1.5 rounded-lg border-slate-300 font-bold">
                            <input type="text" name="recipient_title" id="recipient_title" placeholder="الصفة (فني مصنع/مندوب)"
                                   value="مسؤول الاستلام والمطابقة"
                                   class="text-xs p-1.5 rounded-lg border-slate-300">
                            <input type="tel" name="recipient_phone" id="recipient_phone" placeholder="رقم الهاتف للتواصل"
                                   class="text-xs p-1.5 rounded-lg border-slate-300 font-mono">
                        </div>

                        <!-- Canvas Pad for Recipient -->
                        <div class="relative bg-white rounded-xl border border-slate-300 overflow-hidden shadow-inner h-28">
                            <canvas id="canvas_rc" width="500" height="112" class="w-full h-full cursor-crosshair touch-none"></canvas>
                            <span class="absolute bottom-1 right-2 text-[9px] text-slate-300 pointer-events-none select-none">
                                توقيع المستلم باللمس أو الماوس هنا
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Archival Action in Print -->
                <div class="mt-4 no-print flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-200">
                    <div class="flex-1 ml-4">
                        <input type="text" name="notes" placeholder="ملاحظات أرشفة التقرير المخزني (اختياري)..." class="w-full text-xs p-2 rounded-lg border-amber-300">
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md hover:bg-amber-700 transition inline-flex items-center gap-2">
                        <i class="fa-solid fa-box-archive"></i>
                        <span>تأكيد اعتماد وأرشفة التقرير والتوقيعات بالسيستم</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-[10px] text-slate-400 border-t border-slate-200 pt-3 flex items-center justify-between">
            <span>منظومة إدارة المخازن وحركة الأصناف الرقمية - شركة الفا ايجبت للتعبئة والتغليف © {{ date('Y') }}</span>
            <span class="font-mono">صفحة 1 من 1 | طبع بتاريخ: {{ date('Y-m-d H:i') }}</span>
        </div>
    </div>

    <!-- Canvas Touchpad Signature Scripts & WhatsApp Share -->
    <script>
        function setupSignatureCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let hasDrawn = false;

            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#0f172a';

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                if (e.touches && e.touches.length > 0) {
                    return {
                        x: (e.touches[0].clientX - rect.left) * scaleX,
                        y: (e.touches[0].clientY - rect.top) * scaleY
                    };
                }
                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            }

            function startDraw(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.cancelable && e.type.startsWith('touch')) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                hasDrawn = true;
                if (e.cancelable && e.type.startsWith('touch')) e.preventDefault();
            }

            function endDraw(e) {
                if (isDrawing) {
                    isDrawing = false;
                }
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', endDraw);

            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            window.addEventListener('touchend', endDraw);

            canvas._hasDrawn = () => hasDrawn;
            canvas._clear = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            };
        }

        function clearCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas && canvas._clear) {
                canvas._clear();
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            setupSignatureCanvas('canvas_wm');
            setupSignatureCanvas('canvas_rc');
        });

        // Submit to archive with base64 signatures
        function submitArchivedReport() {
            const canvasWm = document.getElementById('canvas_wm');
            const canvasRc = document.getElementById('canvas_rc');

            if (canvasWm && canvasWm._hasDrawn && canvasWm._hasDrawn()) {
                document.getElementById('wm_signature_input').value = canvasWm.toDataURL('image/png');
            }
            if (canvasRc && canvasRc._hasDrawn && canvasRc._hasDrawn()) {
                document.getElementById('rc_signature_input').value = canvasRc.toDataURL('image/png');
            }

            const recipientName = document.getElementById('recipient_name');
            if (recipientName && !recipientName.value.trim()) {
                recipientName.value = 'مسؤول الاستلام المعتمد';
            }

            document.getElementById('archiveForm').submit();
        }

        document.getElementById('archiveForm').addEventListener('submit', function(e) {
            const canvasWm = document.getElementById('canvas_wm');
            const canvasRc = document.getElementById('canvas_rc');

            if (canvasWm && canvasWm._hasDrawn && canvasWm._hasDrawn()) {
                document.getElementById('wm_signature_input').value = canvasWm.toDataURL('image/png');
            }
            if (canvasRc && canvasRc._hasDrawn && canvasRc._hasDrawn()) {
                document.getElementById('rc_signature_input').value = canvasRc.toDataURL('image/png');
            }
        });

        // Share via WhatsApp
        function shareViaWhatsApp() {
            const recipientPhone = document.getElementById('recipient_phone')?.value?.trim() || '';
            const reportTitle = "{{ $meta['title'] }}";
            const fromDate = "{{ $fromDate }}";
            const toDate = "{{ $toDate }}";
            const totalQty = "{{ number_format($totalQuantity, 2) }}";
            const totalVal = "{{ number_format($totalValue, 2) }} ج.م";
            const currentUrl = window.location.href;

            const message = `*شركة الفا ايجبت للتعبئة والتغليف* 📦\n` +
                `*تقرير المخازن وحركة الأصناف الرسمي*\n` +
                `📌 *التقرير:* ${reportTitle}\n` +
                `📅 *الفترة:* من ${fromDate} إلى ${toDate}\n` +
                `🔢 *إجمالي الكميات:* ${totalQty}\n` +
                `💰 *إجمالي القيمة:* ${totalVal}\n` +
                `🔗 *رابط التقرير المعتمد للمراجعة والتوقيع:*\n${currentUrl}\n\n` +
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
    </script>
</body>
</html>
