@extends('layouts.app')

@section('title', 'تقارير الماكينات كل ساعة')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                <span>تقارير الماكينات كل ساعة (معدل الإنتاج وسرعة السير والكفاءة)</span>
            </h1>
            <p class="text-xs text-slate-500">تقارير لحظية يرفعها المشغل أو مدير الخط للإدارة العليا متضمنة التوصيات الذكية لاتخاذ القرار</p>
        </div>
        <button onclick="document.getElementById('newReportModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>تسجيل تقرير ساعة جديدة</span>
        </button>
    </div>

    <!-- Daily Performance Summary KPI Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي إنتاج اليوم المسجل</p>
                <h3 class="text-xl font-black text-slate-900">{{ number_format($totalProduced) }} <span class="text-xs font-medium text-slate-500">قطعة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">متوسط كفاءة التشغيل OEE</p>
                <h3 class="text-xl font-black text-emerald-700">{{ number_format($avgEfficiency, 1) }}%</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي التوقفات التشغيلية</p>
                <h3 class="text-xl font-black text-amber-700">{{ $totalDowntime }} <span class="text-xs font-medium text-slate-500">دقيقة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي الهادر / التالف</p>
                <h3 class="text-xl font-black text-rose-700">{{ number_format($totalWaste) }} <span class="text-xs font-medium text-slate-500">قطعة</span></h3>
            </div>
        </div>
    </div>

    <!-- Filter by Date & Machine -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
        <form method="GET" action="{{ route('operations.hourlyReports') }}" class="flex flex-wrap items-center justify-between gap-3 m-0">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="font-bold text-slate-700">التاريخ:</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <label class="font-bold text-slate-700">الماكينة:</label>
                    <select name="machine_id" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                        <option value="">كافة الماكينات</option>
                        @foreach($machines as $m)
                            <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-1.5 bg-slate-900 text-white rounded-lg font-bold hover:bg-slate-800 transition">
                    تصفية
                </button>
            </div>

            <!-- Quick Filter Shortcuts -->
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('operations.hourlyReports', ['date' => date('Y-m-d'), 'machine_id' => request('machine_id')]) }}" class="px-2.5 py-1 rounded-lg border {{ $selectedDate === date('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">
                    اليوم
                </a>
                <a href="{{ route('operations.hourlyReports', ['date' => \Carbon\Carbon::yesterday()->toDateString(), 'machine_id' => request('machine_id')]) }}" class="px-2.5 py-1 rounded-lg border {{ $selectedDate === \Carbon\Carbon::yesterday()->toDateString() ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">
                    أمس
                </a>
                <a href="{{ route('operations.hourlyReports', ['date' => 'all', 'machine_id' => request('machine_id')]) }}" class="px-2.5 py-1 rounded-lg border {{ empty($selectedDate) ? 'bg-slate-800 text-white border-slate-800 font-bold' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">
                    عرض كافة التواريخ
                </a>
                <span class="text-slate-400 font-medium mr-2">عدد الساعات المسجلة: <b>{{ $reports->total() }}</b></span>
            </div>
        </form>
    </div>

    <!-- Hourly Reports Cards with AI Decision Recommendations -->
    <div class="space-y-4">
        @forelse($reports as $rep)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ explode(' - ', $rep->hour_slot)[0] ?? 'ساعة' }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $rep->machine->name }}</h3>
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded">{{ $rep->machine->code }}</span>
                                <span class="bg-blue-100 text-blue-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full">{{ $rep->hour_slot }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                المصنع: <b>{{ $rep->factory->name }}</b> | فني التشغيل: <b>{{ $rep->operator_name }}</b> | التاريخ: <b>{{ $rep->report_date->format('Y-m-d') }}</b>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-left">
                            <span class="text-[10px] text-slate-400 block font-bold">كفاءة الماكينة (OEE)</span>
                            <span class="font-black text-base {{ $rep->efficiency_rate >= 90 ? 'text-emerald-600' : ($rep->efficiency_rate >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ $rep->efficiency_rate }}%
                            </span>
                        </div>
                    </div>
                </div>

                @php
                    $activeProd = $rep->active_product;
                @endphp
                @if($activeProd)
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <img src="{{ $activeProd->image_url }}" alt="{{ $activeProd->name }}" class="w-14 h-14 object-cover rounded-xl border border-slate-200 shadow-sm cursor-pointer hover:scale-105 transition flex-shrink-0" onclick="openImageModal('{{ $activeProd->image_url }}')">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] bg-red-600 text-white font-black px-2 py-0.5 rounded">الصنف: {{ $activeProd->code }}</span>
                                    <b class="text-slate-900 font-extrabold text-sm">{{ $activeProd->name }}</b>
                                </div>
                                <div class="flex flex-wrap items-center gap-1 mt-1 text-[10px]">
                                    <span class="text-slate-400 font-bold ml-1">محتويات الكوڤير (BOM):</span>
                                    @foreach($activeProd->boms as $bom)
                                        <span class="bg-white border border-slate-200 px-2 py-0.5 rounded text-slate-700 font-semibold shadow-xs">
                                            {{ $bom->rawMaterial?->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openImageModal('{{ $activeProd->image_url }}')" class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1.5 flex-shrink-0 px-3 py-1.5 bg-white border border-slate-200 rounded-xl hover:shadow transition">
                            <i class="fa-regular fa-image"></i>
                            <span>صورة محتويات الكوڤير</span>
                        </button>
                    </div>
                @endif

                <!-- 4 Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 block mb-0.5">الإنتاج في الساعة</span>
                        <b class="text-slate-900 font-black text-sm">{{ number_format($rep->units_produced) }} قطعة</b>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 block mb-0.5">سرعة الماكينة (بالقطعة)</span>
                        <b class="text-blue-700 font-black text-sm">{{ $rep->belt_speed_m_min }} قطعة/دقيقة</b>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 block mb-0.5">الهادر التالف</span>
                        <b class="{{ $rep->waste_units > 15 ? 'text-rose-600' : 'text-slate-700' }} font-black text-sm">{{ $rep->waste_units }} قطعة</b>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 block mb-0.5">دقائق التوقف</span>
                        <b class="{{ $rep->downtime_minutes > 0 ? 'text-amber-600' : 'text-slate-700' }} font-black text-sm">{{ $rep->downtime_minutes }} دقيقة</b>
                    </div>
                </div>

                @if($rep->downtime_minutes > 0)
                    <div class="text-xs bg-amber-50 text-amber-900 border border-amber-200 p-3 rounded-2xl space-y-1">
                        <div class="flex items-center gap-2 font-bold">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            <span>سبب التوقف خلال الساعة: {{ $rep->downtime_reason }}</span>
                        </div>
                        @if($rep->actions_taken)
                            <div class="text-[11px] text-amber-800"><b>الإجراء المتخذ:</b> {{ $rep->actions_taken }}</div>
                        @endif
                    </div>
                @endif

                <!-- SMART DECISION RECOMMENDATION FOR TOP MANAGEMENT -->
                @if($rep->ai_smart_recommendation)
                    <div class="text-xs bg-emerald-50 text-emerald-950 border border-emerald-200 p-3.5 rounded-2xl flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-sm shadow">
                            <i class="fa-solid fa-brain"></i>
                        </span>
                        <div class="space-y-0.5">
                            <span class="font-black text-emerald-900 text-xs block">التوصية التشغيلية الذكية لاتخاذ القرار (مرفوعة للمدير العام والمالك):</span>
                            <p class="text-[11px] text-emerald-800 leading-relaxed">{{ $rep->ai_smart_recommendation }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center text-slate-400 text-xs border border-slate-200">
                لا توجد تقارير ساعات مسجلة في هذا التاريخ. اضغط على زر (تسجيل تقرير ساعة جديدة) بالأعلى لإضافة تقرير.
            </div>
        @endforelse

        <div class="pt-4">
            {{ $reports->links() }}
        </div>
    </div>

    <!-- Modal to submit new hourly report -->
    <div id="newReportModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-blue-600"></i>
                    <span>تسجيل تقرير ساعة تشغيل للماكينة</span>
                </h3>
                <button onclick="document.getElementById('newReportModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('operations.hourlyReports.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الماكينة *</label>
                        <select name="machine_id" id="report_machine_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                            @foreach($machines as $m)
                                <option value="{{ $m->id }}" data-speed="{{ $m->belt_speed_m_min }}">{{ $m->name }} ({{ $m->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الصنف / المنتج الجاري تشغيله *</label>
                        <select name="product_id" id="report_product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                            <option value="">-- اختر الصنف لمعاينة الصورة والمحتويات --</option>
                            @foreach($products as $prod)
                                @php
                                    $bomNames = $prod->boms->map(fn($b) => $b->rawMaterial?->name)->filter()->values();
                                @endphp
                                <option value="{{ $prod->id }}" 
                                    data-image="{{ $prod->image_url }}" 
                                    data-name="{{ $prod->name }}" 
                                    data-code="{{ $prod->code }}" 
                                    data-components="{{ json_encode($bomNames) }}"
                                    {{ $loop->first ? 'selected' : '' }}>
                                    {{ $prod->name }} ({{ $prod->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Live Product Spec & Visual Card Preview -->
                    <div id="product_preview_box" class="sm:col-span-2 bg-gradient-to-r from-zinc-950 via-slate-900 to-zinc-900 text-white rounded-2xl p-3.5 border border-zinc-700 shadow-md">
                        <div class="flex items-start gap-4">
                            <img id="preview_prod_img" src="" alt="Product" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-zinc-600 shadow cursor-pointer hover:scale-105 transition flex-shrink-0" onclick="openImageModal(this.src)">
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] bg-red-600 text-white font-black px-2 py-0.5 rounded">الصنف النشط</span>
                                    <h4 id="preview_prod_name" class="font-black text-xs sm:text-sm text-zinc-100 truncate"></h4>
                                </div>
                                <p class="text-[11px] text-zinc-400 font-bold">محتويات ومواصفات الكوڤير المعتمدة:</p>
                                <div id="preview_prod_components" class="flex flex-wrap gap-1 text-[10px]"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم المشغل / الفني المسجل *</label>
                        <input type="text" name="operator_name" value="{{ Auth::user()->name }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">تاريخ الوردية *</label>
                            <span class="text-[10px] text-blue-600 font-semibold">متاح اليوم أو بأثر رجعي</span>
                        </div>
                        <input type="date" name="report_date" value="{{ $selectedDate ?: date('Y-m-d') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الفترة الزمنية (الساعة) *</label>
                        <select name="hour_slot" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                            <option value="08:00 - 09:00">08:00 ص - 09:00 ص</option>
                            <option value="09:00 - 10:00">09:00 ص - 10:00 ص</option>
                            <option value="10:00 - 11:00">10:00 ص - 11:00 ص</option>
                            <option value="11:00 - 12:00">11:00 ص - 12:00 م</option>
                            <option value="12:00 - 13:00">12:00 م - 01:00 م</option>
                            <option value="13:00 - 14:00">01:00 م - 02:00 م</option>
                            <option value="14:00 - 15:00">02:00 م - 03:00 م</option>
                            <option value="15:00 - 16:00">03:00 م - 04:00 م</option>
                            <option value="16:00 - 17:00">04:00 م - 05:00 م</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عدد القطع المنتجة في هذه الساعة *</label>
                        <input type="number" name="units_produced" id="report_units_produced" value="2250" placeholder="مثال: 2250" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-blue-700 focus:ring-1 focus:ring-blue-600 outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">سرعة الماكينة (قطعة / دقيقة) *</label>
                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded">بالقطعة</span>
                        </div>
                        <input type="number" step="0.1" name="belt_speed_m_min" id="report_speed" value="45.0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none" placeholder="مثال: 45">
                    </div>

                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">كفاءة الماكينة OEE (%) *</label>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <i class="fa-solid fa-calculator text-[10px]"></i>
                                <span>حساب تلقائي بالمعادلة المعتمدة</span>
                            </span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.01" name="efficiency_rate" id="report_efficiency" value="83.33" readonly required class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-black text-emerald-700 focus:outline-none cursor-not-allowed">
                            <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">%</span>
                        </div>
                    </div>

                    <!-- Live Breakdown of Equation (معادلة إدارة التشغيل) -->
                    <div class="sm:col-span-2 bg-blue-50/80 border border-blue-200 rounded-2xl p-3.5 text-xs text-blue-950 space-y-2">
                        <div class="flex items-center justify-between font-bold border-b border-blue-200/60 pb-1.5">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-square-root-variable text-blue-600"></i>
                                <span>المعادلة المعتمدة لكفاءة الماكينة:</span>
                            </span>
                            <span class="text-[11px] font-mono text-blue-700 dir-ltr">(الإنتاج الفعلي ÷ الطاقة النظرية) × 100</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5 text-center pt-0.5">
                            <div class="bg-white p-2 rounded-xl border border-blue-100 shadow-sm">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الطاقة النظرية (السرعة × 60)</span>
                                <b id="calc_theoretical" class="text-blue-700 font-mono text-xs font-black">2,700 ق/س</b>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-blue-100 shadow-sm">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الإنتاج الفعلي بالساعة</span>
                                <b id="calc_actual" class="text-slate-800 font-mono text-xs font-black">2,250 قطعة</b>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-blue-100 shadow-sm">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الكفاءة المحققة (OEE)</span>
                                <b id="calc_eff_result" class="text-emerald-600 font-mono text-xs font-black">83.33%</b>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عدد القطع التالفة / الهادر</label>
                        <input type="number" name="waste_units" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">مدة التوقف (بالدقائق إن وجدت)</label>
                        <input type="number" name="downtime_minutes" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">سبب التوقف</label>
                    <input type="text" name="downtime_reason" placeholder="مثال: تغيير بكرة سيلوفان، نظافة فكوك لحام..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الإجراءات المتخذة لمعالجة المشكلة</label>
                    <input type="text" name="actions_taken" placeholder="تمت إعادة ضبط السكينة واستئناف العمل فوراً..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('newReportModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow">
                        حفظ التقرير ورفعه للإدارة العليا
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function calculateOEE() {
        const speedInput = document.getElementById('report_speed');
        const unitsInput = document.getElementById('report_units_produced');
        const effInput = document.getElementById('report_efficiency');
        
        const speed = parseFloat(speedInput ? speedInput.value : 0) || 0;
        const units = parseFloat(unitsInput ? unitsInput.value : 0) || 0;
        
        // Theoretical Hourly Capacity = Speed (pieces/min) * 60 minutes
        const theoreticalHour = speed * 60;
        
        let efficiency = 0;
        if (theoreticalHour > 0) {
            efficiency = (units / theoreticalHour) * 100;
        }
        
        const formattedEff = efficiency.toFixed(2);
        if (effInput) effInput.value = formattedEff;
        
        // Update live breakdown display
        const calcTheo = document.getElementById('calc_theoretical');
        const calcAct = document.getElementById('calc_actual');
        const calcRes = document.getElementById('calc_eff_result');
        
        if (calcTheo) calcTheo.innerText = theoreticalHour.toLocaleString('en-US') + ' ق/س';
        if (calcAct) calcAct.innerText = units.toLocaleString('en-US') + ' قطعة';
        if (calcRes) {
            calcRes.innerText = formattedEff + '%';
            if (efficiency >= 90) {
                calcRes.className = 'text-emerald-600 font-mono text-xs font-black';
            } else if (efficiency >= 75) {
                calcRes.className = 'text-amber-600 font-mono text-xs font-black';
            } else {
                calcRes.className = 'text-rose-600 font-mono text-xs font-black';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const speedInput = document.getElementById('report_speed');
        const unitsInput = document.getElementById('report_units_produced');
        const machineSelect = document.getElementById('report_machine_id');
        
        if (speedInput && unitsInput) {
            speedInput.addEventListener('input', calculateOEE);
            unitsInput.addEventListener('input', calculateOEE);
        }
        
        if (machineSelect && speedInput) {
            machineSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const speed = opt.getAttribute('data-speed');
                if (speed && parseFloat(speed) > 0) {
                    speedInput.value = parseFloat(speed);
                    calculateOEE();
                }
            });
        }
        
        calculateOEE();

        // Product specification card live preview in modal
        const productSelect = document.getElementById('report_product_id');
        function updateProductPreview() {
            const previewBox = document.getElementById('product_preview_box');
            const img = document.getElementById('preview_prod_img');
            const name = document.getElementById('preview_prod_name');
            const compContainer = document.getElementById('preview_prod_components');

            if (!productSelect || !productSelect.value) {
                if (previewBox) previewBox.classList.add('hidden');
                return;
            }

            const opt = productSelect.options[productSelect.selectedIndex];
            const imgSrc = opt.getAttribute('data-image');
            const prodName = opt.getAttribute('data-name');
            let components = [];
            try {
                components = JSON.parse(opt.getAttribute('data-components') || '[]');
            } catch(e) {}

            if (img && imgSrc) img.src = imgSrc;
            if (name && prodName) name.innerText = prodName;
            if (compContainer) {
                compContainer.innerHTML = '';
                components.forEach(c => {
                    const pill = document.createElement('span');
                    pill.className = 'bg-zinc-800 border border-zinc-700 px-2 py-0.5 rounded text-zinc-300 font-semibold';
                    pill.innerText = c;
                    compContainer.appendChild(pill);
                });
            }
            if (previewBox) previewBox.classList.remove('hidden');
        }

        if (productSelect) {
            productSelect.addEventListener('change', updateProductPreview);
            updateProductPreview();
        }
    });

    // Image Zoom Modal Functions
    function openImageModal(src) {
        if (!src) return;
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('modalPreviewImage');
        if (img) img.src = src;
        if (modal) modal.classList.remove('hidden');
    }

    function closeImageModal() {
        const modal = document.getElementById('imagePreviewModal');
        if (modal) modal.classList.add('hidden');
    }
</script>

<!-- Full Image Zoom Modal -->
<div id="imagePreviewModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-2xl w-full bg-zinc-950 rounded-3xl p-3 shadow-2xl border border-zinc-800 overflow-hidden" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute top-4 left-4 z-10 w-9 h-9 rounded-full bg-black/80 hover:bg-black text-white flex items-center justify-center transition border border-white/20">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
        <img id="modalPreviewImage" src="" alt="Specification Visual" class="w-full h-auto max-h-[82vh] object-contain rounded-2xl">
        <div class="p-3 text-center text-xs font-bold text-zinc-300 border-t border-zinc-800/80 mt-2">
            بطاقة المواصفات التعريفية المعتمدة لمكونات الصنف — الفا ايجبت للصناعات المتطورة
        </div>
    </div>
</div>
@endsection
