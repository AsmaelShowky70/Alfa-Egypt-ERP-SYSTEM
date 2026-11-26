@extends('layouts.app')

@section('title', 'تقارير فحص وتشغيل الماكينات كل ساعة')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-gauge-high text-red-600"></i>
                <span>تقارير فحص وتشغيل الماكينات كل ساعة (متابعة الأداء، الورديات، والتحليلات الشاملة)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                نظام تشغيلي متكامل يتيح مرونة الساعات والورديات، فلاتر متعددة بنمط الإكسيل، كشف أداء الماكينات الشهري، ومتابعة فترات التوقف والخمول
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Excel CSV Export Button -->
            <a href="{{ route('operations.hourlyReports.export', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-2"
               title="تحميل كشيت إكسيل مطابق للفلاتر المختارة">
                <i class="fa-solid fa-file-excel text-sm"></i>
                <span>تصدير إكسيل (CSV)</span>
            </a>

            <!-- Print Button -->
            <button onclick="window.print()" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>طباعة الكشف</span>
            </button>

            <!-- New Report Modal Button -->
            <button onclick="document.getElementById('newReportModal').classList.remove('hidden')" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل ساعة تشغيل جديدة</span>
            </button>
        </div>
    </div>

    <!-- Live Performance Summary KPI Bar (Based on Applied Filters) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي الإنتاج الفعلي</p>
                <h3 class="text-xl font-black text-slate-900">{{ number_format($totalProduced) }} <span class="text-xs font-medium text-slate-500">قطعة</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">متوسط كفاءة التشغيل OEE</p>
                <h3 class="text-xl font-black text-emerald-700">{{ number_format($avgEfficiency, 1) }}%</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">ساعات بدون أعطال (صافي)</p>
                <h3 class="text-xl font-black text-teal-700">{{ $zeroDowntimeRate }}% <span class="text-[10px] font-bold text-slate-400">({{ $zeroDowntimeCount }}/{{ $totalRecords }} ساعة)</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي دقائق التوقف</p>
                <h3 class="text-xl font-black text-amber-700">{{ $totalDowntime }} <span class="text-xs font-medium text-slate-500">دقيقة</span> <span class="text-[10px] text-slate-400 font-bold">({{ round($totalDowntime / 60, 1) }} س)</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400">إجمالي الهادر التالف</p>
                <h3 class="text-xl font-black text-rose-700">{{ number_format($totalWaste) }} <span class="text-xs font-medium text-slate-500">قطعة</span></h3>
            </div>
        </div>
    </div>

    <!-- Shifts Output Breakdown Bar (توزيع إنتاج الورديات) -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-arrows-spin text-red-600"></i>
                <h3 class="text-sm font-black text-slate-900">توزيع إنتاج الورديات (تغطية 24 ساعة):</h3>
            </div>
            <span class="text-[11px] text-slate-400 font-bold">الفترة: {{ $dateFrom ?: 'من البداية' }} إلى {{ $dateTo ?: 'الآن' }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <!-- Shift 1 -->
            <div class="p-3.5 rounded-2xl border-2 flex items-center justify-between {{ ($shift === '1') ? 'bg-amber-50 border-amber-500 ring-2 ring-amber-400/30' : 'bg-slate-50/70 border-slate-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-sun"></i>
                    </div>
                    <div>
                        <b class="text-xs text-slate-900 block">الوردية الأولى (صباحية)</b>
                        <span class="text-[10px] text-slate-500">08:00 ص - 04:00 م</span>
                    </div>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block font-bold">الإنتاج</span>
                    <b class="text-sm font-black text-amber-900">{{ number_format($shift1Output) }}</b>
                </div>
            </div>

            <!-- Shift 2 -->
            <div class="p-3.5 rounded-2xl border-2 flex items-center justify-between {{ ($shift === '2') ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-400/30' : 'bg-slate-50/70 border-slate-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-cloud-sun"></i>
                    </div>
                    <div>
                        <b class="text-xs text-slate-900 block">الوردية الثانية (مسائية)</b>
                        <span class="text-[10px] text-slate-500">04:00 م - 12:00 ص</span>
                    </div>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block font-bold">الإنتاج</span>
                    <b class="text-sm font-black text-blue-900">{{ number_format($shift2Output) }}</b>
                </div>
            </div>

            <!-- Shift 3 -->
            <div class="p-3.5 rounded-2xl border-2 flex items-center justify-between {{ ($shift === '3') ? 'bg-indigo-50 border-indigo-500 ring-2 ring-indigo-400/30' : 'bg-slate-50/70 border-slate-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-moon"></i>
                    </div>
                    <div>
                        <b class="text-xs text-slate-900 block">الوردية الثالثة (ليلية)</b>
                        <span class="text-[10px] text-slate-500">12:00 ص - 08:00 ص</span>
                    </div>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block font-bold">الإنتاج</span>
                    <b class="text-sm font-black text-indigo-900">{{ number_format($shift3Output) }}</b>
                </div>
            </div>

            <!-- Shift Custom / Extra -->
            <div class="p-3.5 rounded-2xl border-2 flex items-center justify-between {{ ($shift === 'custom') ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-400/30' : 'bg-slate-50/70 border-slate-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <b class="text-xs text-slate-900 block">وردية مخصصة / إضافية</b>
                        <span class="text-[10px] text-slate-500">ساعات مرنة مخصصة</span>
                    </div>
                </div>
                <div class="text-left">
                    <span class="text-[10px] text-slate-400 block font-bold">الإنتاج</span>
                    <b class="text-sm font-black text-emerald-900">{{ number_format($shiftCustomOutput) }}</b>
                </div>
            </div>
        </div>
    </div>

    <!-- View Mode Switcher Tabs (3 Distinct Perspectives) -->
    <div class="flex items-center justify-between bg-white p-2 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-2">
            <button type="button" onclick="switchViewMode('grid')" id="tab_btn_grid" class="view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $viewMode === 'grid' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-table-cells text-sm"></i>
                <span>جدول البيانات التفاعلي (نمط الإكسيل مع الفلاتر)</span>
            </button>

            <button type="button" onclick="switchViewMode('analytics')" id="tab_btn_analytics" class="view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $viewMode === 'analytics' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-industry text-sm"></i>
                <span>كشف أداء الماكينات (الشهري واليومي والخمول)</span>
            </button>

            <button type="button" onclick="switchViewMode('cards')" id="tab_btn_cards" class="view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 {{ $viewMode === 'cards' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-id-card text-sm"></i>
                <span>بطاقات الساعات التفصيلية (صور المنتجات والـ BOM)</span>
            </button>
        </div>

        <div class="text-xs text-slate-400 font-bold hidden sm:block px-3">
            إجمالي السجلات: <span class="text-slate-900 font-black">{{ $reports->total() }}</span> ساعة مسجلة
        </div>
    </div>

    <!-- Multi-Column Excel Filter Toolbar (شريط الفلاتر الذكي بنمط الإكسيل) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-filter text-red-600"></i>
                <h3 class="text-xs font-black text-slate-900">شريط الفلاتر المتقدمة الشاملة (فلترة متعددة على مستوى كل عمود):</h3>
            </div>
            
            <!-- Quick Date Filter Shortcuts -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-[10px] text-slate-400 font-bold ml-1">فترات سريعة:</span>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'page']), ['period' => 'today'])) }}" class="px-2.5 py-1 rounded-lg border {{ ($period === 'today' || ($dateFrom === date('Y-m-d') && $dateTo === date('Y-m-d'))) ? 'bg-red-600 text-white border-red-600 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">اليوم</a>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'page']), ['period' => 'yesterday'])) }}" class="px-2.5 py-1 rounded-lg border {{ $period === 'yesterday' ? 'bg-red-600 text-white border-red-600 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">أمس</a>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'page']), ['period' => 'this_week'])) }}" class="px-2.5 py-1 rounded-lg border {{ $period === 'this_week' ? 'bg-red-600 text-white border-red-600 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">هذا الأسبوع</a>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'page']), ['period' => 'this_month'])) }}" class="px-2.5 py-1 rounded-lg border {{ $period === 'this_month' ? 'bg-red-600 text-white border-red-600 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">هذا الشهر</a>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'page']), ['period' => 'last_month'])) }}" class="px-2.5 py-1 rounded-lg border {{ $period === 'last_month' ? 'bg-red-600 text-white border-red-600 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">الشهر السابق</a>
                <a href="{{ route('operations.hourlyReports', array_merge(request()->except(['date_from', 'date_to', 'date', 'period', 'page']), ['period' => 'all'])) }}" class="px-2.5 py-1 rounded-lg border {{ $period === 'all' ? 'bg-slate-900 text-white border-slate-900 font-black' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} transition">كافة التواريخ</a>
            </div>
        </div>

        <form method="GET" action="{{ route('operations.hourlyReports') }}" id="filterForm" class="space-y-4 m-0 text-xs">
            <input type="hidden" name="view" id="filter_view_input" value="{{ $viewMode }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Date From -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">من تاريخ:</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">إلى تاريخ:</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                </div>

                <!-- Machine Filter -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">الماكينة (المكن):</label>
                    <select name="machine_id" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                        <option value="">كافة الماكينات</option>
                        @foreach($machines as $m)
                            <option value="{{ $m->id }}" {{ $machineId == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Filter -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">الوردية:</label>
                    <select name="shift" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                        <option value="all" {{ ($shift === 'all' || empty($shift)) ? 'selected' : '' }}>كافة الورديات</option>
                        <option value="1" {{ $shift === '1' ? 'selected' : '' }}>☀️ وردية 1 (الصباحية)</option>
                        <option value="2" {{ $shift === '2' ? 'selected' : '' }}>🌆 وردية 2 (المسائية)</option>
                        <option value="3" {{ $shift === '3' ? 'selected' : '' }}>🌙 وردية 3 (الليلية)</option>
                        <option value="custom" {{ $shift === 'custom' ? 'selected' : '' }}>⚡ وردية مخصصة / إضافية</option>
                    </select>
                </div>

                <!-- Downtime / Breakdown Filter (فكرة البلانكة والفاضي) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-red-700 flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>فلتر الأعطال والتوقفات:</span>
                    </label>
                    <select name="downtime_filter" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none bg-slate-50">
                        <option value="all" {{ ($downtimeFilter === 'all' || empty($downtimeFilter)) ? 'selected' : '' }}>كافة الساعات (تشغيل وأعطال)</option>
                        <option value="zero" {{ ($downtimeFilter === 'zero' || $downtimeFilter === 'blank') ? 'selected' : '' }}>🟢 ساعات خالية من الأعطال (بلانكة / بدون توقف)</option>
                        <option value="has_downtime" {{ $downtimeFilter === 'has_downtime' ? 'selected' : '' }}>🔴 ساعات بها أعطال وتوقف فقط</option>
                    </select>
                </div>

                <!-- Efficiency OEE Filter -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">كفاءة التشغيل OEE:</label>
                    <select name="eff_filter" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                        <option value="all" {{ ($effFilter === 'all' || empty($effFilter)) ? 'selected' : '' }}>كافة النسب</option>
                        <option value="high" {{ $effFilter === 'high' ? 'selected' : '' }}>🟢 كفاءة مثالية (90% فأعلى)</option>
                        <option value="medium" {{ $effFilter === 'medium' ? 'selected' : '' }}>🟡 كفاءة مقبولة (75% - 89%)</option>
                        <option value="low" {{ $effFilter === 'low' ? 'selected' : '' }}>🔴 كفاءة منخفضة (أقل من 75%)</option>
                    </select>
                </div>

                <!-- Product Filter -->
                <div class="lg:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">الصنف / المنتج:</label>
                    <select name="product_id" class="w-full px-3 py-1.5 border border-slate-200 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                        <option value="">كافة الأصناف المسجلة</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Operator Search -->
                <div class="lg:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">اسم المشغل / الفني:</label>
                    <input type="text" name="operator" value="{{ $operatorSearch }}" placeholder="بحث باسم المشغل المسجل..." class="w-full px-3 py-1.5 border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-600 outline-none">
                </div>

                <!-- Action Filter Buttons -->
                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-black transition flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>تطبيق الفلترة</span>
                    </button>
                    <a href="{{ route('operations.hourlyReports') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition flex items-center gap-1" title="إعادة تعيين الفلاتر">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>إعادة تعيين</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- ================= VIEW 1: EXCEL-LIKE DATA GRID ================= -->
    <div id="view_grid_container" class="{{ $viewMode === 'grid' ? '' : 'hidden' }} space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-table text-red-600"></i>
                    <h3 class="font-black text-slate-900">جدول السجلات المفصلة (مطابق تماماً لنمط الإكسيل مع تجميع تلقائي):</h3>
                </div>
                <div class="flex items-center gap-2 text-slate-500 font-bold">
                    <span>عدد السجلات المعروضة: <b>{{ $reports->count() }}</b> من أصل <b>{{ $reports->total() }}</b></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-100/90 text-slate-700 border-b border-slate-200 font-black text-[11px] whitespace-nowrap">
                            <th class="py-3 px-3 text-center">م</th>
                            <th class="py-3 px-3">التاريخ</th>
                            <th class="py-3 px-3">الوردية</th>
                            <th class="py-3 px-3">الفترة (الساعة)</th>
                            <th class="py-3 px-3">الماكينة</th>
                            <th class="py-3 px-3">الصنف المشغل ومحتوياته</th>
                            <th class="py-3 px-3 text-center">الإنتاج (قطعة)</th>
                            <th class="py-3 px-3 text-center">السرعة (قطعة/د)</th>
                            <th class="py-3 px-3 text-center">كفاءة OEE</th>
                            <th class="py-3 px-3 text-center">الهادر (قطعة)</th>
                            <th class="py-3 px-3 text-center">التوقف (دقيقة)</th>
                            <th class="py-3 px-3">سبب التوقف</th>
                            <th class="py-3 px-3">الإجراء المتخذ</th>
                            <th class="py-3 px-3">المشغل</th>
                            <th class="py-3 px-3 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800 text-[11px]">
                        @forelse($reports as $index => $rep)
                            <tr class="hover:bg-slate-50/80 transition {{ $rep->downtime_minutes > 0 ? 'bg-amber-50/30' : '' }}">
                                <td class="py-3 px-3 text-center font-bold text-slate-400">
                                    {{ $reports->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3 font-bold whitespace-nowrap">
                                    {{ $rep->report_date ? $rep->report_date->format('Y-m-d') : '-' }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black border {{ $rep->shift_badge_class }}">
                                        {{ $rep->shift_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900 whitespace-nowrap dir-ltr text-right">
                                    {{ $rep->hour_slot }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-extrabold text-slate-900">{{ $rep->machine->name }}</div>
                                    <span class="text-[10px] text-slate-400">{{ $rep->machine->code }}</span>
                                </td>
                                <td class="py-3 px-3 min-w-[200px]">
                                    @php $actProd = $rep->active_product; @endphp
                                    @if($actProd)
                                        <div class="flex items-center gap-2">
                                            @if($actProd->image_url)
                                                <img src="{{ $actProd->image_url }}" alt="{{ $actProd->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 cursor-pointer flex-shrink-0" onclick="openImageModal('{{ $actProd->image_url }}')">
                                            @endif
                                            <div>
                                                <b class="text-slate-900 block truncate max-w-[180px]">{{ $actProd->name }}</b>
                                                <span class="text-[10px] text-slate-400">{{ $actProd->code }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">غير محدد</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-black text-slate-900 font-mono text-xs whitespace-nowrap">
                                    {{ number_format($rep->units_produced) }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-blue-700 font-mono whitespace-nowrap">
                                    {{ $rep->belt_speed_m_min }}
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg font-black text-xs font-mono {{ $rep->efficiency_rate >= 90 ? 'bg-emerald-100 text-emerald-800' : ($rep->efficiency_rate >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $rep->efficiency_rate }}%
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-bold font-mono whitespace-nowrap {{ $rep->waste_units > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    {{ $rep->waste_units }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold font-mono whitespace-nowrap {{ $rep->downtime_minutes > 0 ? 'text-amber-700 bg-amber-100/60 rounded' : 'text-slate-400' }}">
                                    {{ $rep->downtime_minutes }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-[180px] truncate" title="{{ $rep->downtime_reason }}">
                                    {{ $rep->downtime_reason ?: 'تشغيل منتظم' }}
                                </td>
                                <td class="py-3 px-3 text-slate-500 max-w-[180px] truncate" title="{{ $rep->actions_taken }}">
                                    {{ $rep->actions_taken ?: '-' }}
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-700 whitespace-nowrap">
                                    {{ $rep->operator_name }}
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if($rep->is_editable)
                                        <button type="button" onclick="openEditReportModal({{ json_encode([
                                            'id' => $rep->id,
                                            'machine_name' => $rep->machine->name,
                                            'machine_code' => $rep->machine->code,
                                            'shift' => $rep->shift ?? '1',
                                            'hour_slot' => $rep->hour_slot,
                                            'report_date' => $rep->report_date->format('Y-m-d'),
                                            'operator_name' => $rep->operator_name,
                                            'product_id' => $rep->product_id,
                                            'units_produced' => (float)$rep->units_produced,
                                            'belt_speed_m_min' => (float)$rep->belt_speed_m_min,
                                            'waste_units' => (float)$rep->waste_units,
                                            'downtime_minutes' => (int)$rep->downtime_minutes,
                                            'downtime_reason' => $rep->downtime_reason,
                                            'actions_taken' => $rep->actions_taken,
                                        ]) }})" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition" title="تعديل بيانات الساعة">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    @else
                                        <span class="text-slate-300" title="مغلق لانتهاء اليوم"><i class="fa-solid fa-lock"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="py-12 text-center text-slate-400 font-bold">
                                    لا توجد بيانات مطابقة للفلاتر المختارة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- Totals Summary Footer Row -->
                    @if($reports->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-900 text-white font-black text-xs border-t-2 border-slate-900 whitespace-nowrap">
                                <td colspan="6" class="py-3.5 px-3 text-center text-slate-300">
                                    المجاميع المجمعة للفلاتر المحددة (Summary Totals):
                                </td>
                                <td class="py-3.5 px-3 text-center text-emerald-400 font-mono text-sm">
                                    {{ number_format($totalProduced) }}
                                </td>
                                <td class="py-3.5 px-3 text-center text-slate-300 font-mono">
                                    -
                                </td>
                                <td class="py-3.5 px-3 text-center text-emerald-400 font-mono text-sm">
                                    {{ number_format($avgEfficiency, 1) }}%
                                </td>
                                <td class="py-3.5 px-3 text-center text-rose-400 font-mono text-sm">
                                    {{ number_format($totalWaste) }}
                                </td>
                                <td class="py-3.5 px-3 text-center text-amber-400 font-mono text-sm">
                                    {{ number_format($totalDowntime) }}
                                </td>
                                <td colspan="4" class="py-3.5 px-3 text-left text-slate-400 text-[10px] pl-4">
                                    نسبة الساعات الخالية من الأعطال: <b class="text-teal-300">{{ $zeroDowntimeRate }}%</b>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <!-- ================= VIEW 2: MACHINE PERFORMANCE & ANALYTICS ================= -->
    <div id="view_analytics_container" class="{{ $viewMode === 'analytics' ? '' : 'hidden' }} space-y-5">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-industry text-red-600"></i>
                        <span>كشف أداء الماكينات (الإنتاج اليومي، إنتاج الشهر، نسب الأعطال، وتتبع فترات الخمول والتوقف التام)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        تحليل تفصيلي لكل ماكينة لمعرفة الإنتاج الفعلي، الأيام التي لم تعمل فيها نهائياً، ومتوسط الكفاءة بدون أعطال
                    </p>
                </div>
            </div>

            <!-- Machine Analytics Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                @foreach($machineAnalytics as $ma)
                    <div class="bg-slate-50/70 border border-slate-200 rounded-3xl p-5 space-y-4 hover:border-slate-300 transition relative overflow-hidden">
                        <!-- Machine Header -->
                        <div class="flex items-start justify-between gap-3 border-b border-slate-200/80 pb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ $ma->days_since_active === 0 ? 'bg-emerald-500 animate-pulse' : ($ma->is_alarm ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                    <h4 class="font-black text-sm text-slate-900">{{ $ma->machine->name }}</h4>
                                </div>
                                <span class="text-[10px] bg-slate-200/80 text-slate-700 font-bold px-2 py-0.5 rounded mt-1 inline-block">{{ $ma->machine->code }} | {{ $ma->machine->factory?->name }}</span>
                            </div>

                            <!-- Inactivity / Idle Status Badge -->
                            <div>
                                @if($ma->days_since_active === 0)
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-black text-[10px] border border-emerald-300 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>تعمل اليوم</span>
                                    </span>
                                @elseif($ma->days_since_active !== null)
                                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-black text-[10px] border border-rose-300 flex items-center gap-1" title="لم تسجل أي إنتاج منذ هذا التاريخ">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>متوقفة منذ {{ $ma->days_since_active }} يوم!</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-200 text-slate-600 rounded-full font-bold text-[10px]">
                                        لم تبدأ بعد
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 4 Core Machine Metrics -->
                        <div class="grid grid-cols-2 gap-2.5 text-center text-xs">
                            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-xs">
                                <span class="text-[10px] text-slate-400 block font-bold mb-0.5">إنتاج اليوم</span>
                                <b class="text-sm font-black text-slate-900 font-mono">{{ number_format($ma->today_output) }} <span class="text-[10px] font-normal text-slate-500">ق</span></b>
                            </div>

                            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-xs">
                                <span class="text-[10px] text-slate-400 block font-bold mb-0.5">إنتاج الشهر الحالي</span>
                                <b class="text-sm font-black text-blue-700 font-mono">{{ number_format($ma->month_output) }} <span class="text-[10px] font-normal text-slate-500">ق</span></b>
                            </div>

                            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-xs">
                                <span class="text-[10px] text-slate-400 block font-bold mb-0.5">متوسط OEE بالشهر</span>
                                <b class="text-sm font-black font-mono {{ $ma->month_avg_oee >= 90 ? 'text-emerald-600' : ($ma->month_avg_oee >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ number_format($ma->month_avg_oee, 1) }}%
                                </b>
                            </div>

                            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-xs">
                                <span class="text-[10px] text-slate-400 block font-bold mb-0.5">تشغيل بدون أعطال</span>
                                <b class="text-sm font-black text-teal-700 font-mono">{{ $ma->zero_breakdown_rate }}%</b>
                            </div>
                        </div>

                        <!-- Additional Monthly Performance Details -->
                        <div class="space-y-1.5 text-xs bg-white p-3 rounded-2xl border border-slate-200/70">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>ساعات التشغيل بالشهر:</span>
                                <b class="font-mono text-slate-900">{{ $ma->month_hours }} ساعة</b>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>توقفات وأعطال الشهر:</span>
                                <b class="font-mono text-amber-700">{{ $ma->month_downtime }} دقيقة ({{ round($ma->month_downtime / 60, 1) }} س)</b>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>أيام التشغيل الفعلية بالشهر:</span>
                                <b class="font-mono text-emerald-700">{{ $ma->active_days_this_month }} يوم</b>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>أيام خمول وتوقف تام بالشهر:</span>
                                <b class="font-mono {{ $ma->idle_days_this_month > 5 ? 'text-rose-600' : 'text-slate-700' }}">{{ $ma->idle_days_this_month }} يوم</b>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 border-t border-slate-100 pt-1.5">
                                <span>إجمالي الإنتاج التراكمي:</span>
                                <b class="font-mono text-slate-900 font-black">{{ number_format($ma->total_output) }} قطعة</b>
                            </div>
                            <div class="flex items-center justify-between text-slate-400 text-[10px]">
                                <span>آخر تاريخ تسجيل:</span>
                                <b>{{ $ma->last_active_date ?: 'لا يوجد' }}</b>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ================= VIEW 3: DETAILED CARDS VIEW ================= -->
    <div id="view_cards_container" class="{{ $viewMode === 'cards' ? '' : 'hidden' }} space-y-4">
        @forelse($reports as $rep)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4 hover:border-slate-300 transition">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-black text-xs flex-shrink-0 {{ ($rep->shift ?? '1') == '1' ? 'bg-amber-100 text-amber-800 border border-amber-200' : (($rep->shift ?? '1') == '2' ? 'bg-blue-100 text-blue-800 border border-blue-200' : (($rep->shift ?? '1') == '3' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200')) }}">
                            @if(($rep->shift ?? '1') == '1')
                                <i class="fa-solid fa-sun text-sm mb-0.5"></i>
                                <span class="text-[9px]">وردية 1</span>
                            @elseif(($rep->shift ?? '1') == '2')
                                <i class="fa-solid fa-cloud-sun text-sm mb-0.5"></i>
                                <span class="text-[9px]">وردية 2</span>
                            @elseif(($rep->shift ?? '1') == '3')
                                <i class="fa-solid fa-moon text-sm mb-0.5"></i>
                                <span class="text-[9px]">وردية 3</span>
                            @else
                                <i class="fa-solid fa-bolt text-sm mb-0.5"></i>
                                <span class="text-[9px]">مخصصة</span>
                            @endif
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $rep->machine->name }}</h3>
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded">{{ $rep->machine->code }}</span>
                                <span class="bg-slate-900 text-white text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-full dir-ltr">
                                    {{ $rep->hour_slot }}
                                </span>
                                <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full border {{ $rep->shift_badge_class }}">
                                    {{ $rep->shift_name }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                المصنع: <b>{{ $rep->factory->name }}</b> | فني التشغيل: <b>{{ $rep->operator_name }}</b> | التاريخ: <b>{{ $rep->report_date->format('Y-m-d') }}</b>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-left bg-slate-50 border border-slate-200/80 px-3 py-1.5 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-bold">كفاءة الماكينة (OEE)</span>
                            <span class="font-black text-base {{ $rep->efficiency_rate >= 90 ? 'text-emerald-600' : ($rep->efficiency_rate >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ $rep->efficiency_rate }}%
                            </span>
                        </div>

                        @if($rep->is_editable)
                            <button type="button" onclick="openEditReportModal({{ json_encode([
                                'id' => $rep->id,
                                'machine_name' => $rep->machine->name,
                                'machine_code' => $rep->machine->code,
                                'shift' => $rep->shift ?? '1',
                                'hour_slot' => $rep->hour_slot,
                                'report_date' => $rep->report_date->format('Y-m-d'),
                                'operator_name' => $rep->operator_name,
                                'product_id' => $rep->product_id,
                                'units_produced' => (float)$rep->units_produced,
                                'belt_speed_m_min' => (float)$rep->belt_speed_m_min,
                                'waste_units' => (float)$rep->waste_units,
                                'downtime_minutes' => (int)$rep->downtime_minutes,
                                'downtime_reason' => $rep->downtime_reason,
                                'actions_taken' => $rep->actions_taken,
                            ]) }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded-xl border border-blue-200 transition flex items-center gap-1.5 shadow-xs">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>تعديل الساعة</span>
                            </button>
                        @else
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-xl border border-slate-200 flex items-center gap-1.5" title="التقرير مقفول بانتهاء اليوم">
                                <i class="fa-solid fa-lock text-slate-400"></i>
                                <span>مغلق لانتهاء اليوم</span>
                            </span>
                        @endif
                    </div>
                </div>

                @php $activeProd = $rep->active_product; @endphp
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
                                    <span class="text-slate-400 font-bold ml-1">محتويات ومواصفات الكوڤير (BOM):</span>
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
            <div class="bg-white rounded-3xl p-12 text-center text-slate-400 text-xs border border-slate-200 space-y-2">
                <i class="fa-solid fa-clipboard-list text-3xl text-slate-300 block mb-2"></i>
                <p class="font-bold text-slate-600">لا توجد تقارير ساعات مسجلة مطابقة للبحث الحالي.</p>
                <p>اضغط على زر (تسجيل ساعة تشغيل جديدة) بالأعلى للبدء في تدوين ساعات الوردية.</p>
            </div>
        @endforelse

        <div class="pt-4">
            {{ $reports->links() }}
        </div>
    </div>

    <!-- ================= ULTRA-FLEXIBLE MODAL: SUBMIT NEW HOURLY REPORT ================= -->
    <div id="newReportModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-red-600"></i>
                        <span>تسجيل تقرير ساعة تشغيل للماكينة</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">مرونة تامة في اختيار الوردية (1، 2، 3 أو مخصصة) ونظام الساعات (8، 10، 12 أو إدخال حر)</p>
                </div>
                <button type="button" onclick="document.getElementById('newReportModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('operations.hourlyReports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Shift Selection Cards -->
                <div class="space-y-2 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-slate-900">
                            تحديد الوردية التشغيلية للماكينة *
                        </label>
                        <span class="text-[10px] text-slate-500 font-bold">تحديث تلقائي لقائمة الساعات</span>
                    </div>
                    <input type="hidden" name="shift" id="report_shift_input" value="1">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Shift 1 Button -->
                        <button type="button" onclick="setShiftInModal('1')" id="modal_shift_btn_1" class="shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-amber-500 bg-amber-50/90 ring-2 ring-amber-400/30">
                            <div>
                                <b class="text-xs text-slate-900 block">وردية 1</b>
                                <span class="text-[10px] text-slate-500">الصباحية</span>
                            </div>
                            <span id="modal_shift_check_1" class="w-4 h-4 rounded-full bg-amber-600 text-white flex items-center justify-center text-[9px]">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </button>

                        <!-- Shift 2 Button -->
                        <button type="button" onclick="setShiftInModal('2')" id="modal_shift_btn_2" class="shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-slate-200 bg-white hover:border-blue-300">
                            <div>
                                <b class="text-xs text-slate-900 block">وردية 2</b>
                                <span class="text-[10px] text-slate-500">المسائية</span>
                            </div>
                            <span id="modal_shift_check_2" class="hidden w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px]">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </button>

                        <!-- Shift 3 Button -->
                        <button type="button" onclick="setShiftInModal('3')" id="modal_shift_btn_3" class="shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-slate-200 bg-white hover:border-indigo-300">
                            <div>
                                <b class="text-xs text-slate-900 block">وردية 3</b>
                                <span class="text-[10px] text-slate-500">الليلية</span>
                            </div>
                            <span id="modal_shift_check_3" class="hidden w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[9px]">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </button>

                        <!-- Shift Custom / Open -->
                        <button type="button" onclick="setShiftInModal('custom')" id="modal_shift_btn_custom" class="shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-slate-200 bg-white hover:border-emerald-300">
                            <div>
                                <b class="text-xs text-slate-900 block">وردية مخصصة</b>
                                <span class="text-[10px] text-slate-500">ساعات مفتوحة</span>
                            </div>
                            <span id="modal_shift_check_custom" class="hidden w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[9px]">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Shift Preset Duration Selector (8h, 10h, 12h, or Custom) -->
                <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs">
                    <span class="font-bold text-slate-700 text-[11px]">نظام عدد ساعات الوردية:</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setShiftHoursMode('8')" id="dur_btn_8" class="px-2.5 py-1 rounded-lg text-[11px] font-black bg-red-600 text-white transition">8 ساعات</button>
                        <button type="button" onclick="setShiftHoursMode('10')" id="dur_btn_10" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 transition">10 ساعات (07ص - 05م)</button>
                        <button type="button" onclick="setShiftHoursMode('12')" id="dur_btn_12" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 transition">12 ساعة (07ص - 07م)</button>
                        <button type="button" onclick="toggleCustomHourSlot(true)" id="dur_btn_free" class="px-2.5 py-1 rounded-lg text-[11px] font-black bg-white text-blue-700 border border-blue-200 hover:bg-blue-50 transition">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span>إدخال حر/مخصص</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الماكينة *</label>
                        <select name="machine_id" id="report_machine_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-red-600 outline-none">
                            @foreach($machines as $m)
                                <option value="{{ $m->id }}" data-speed="{{ $m->belt_speed_m_min }}">{{ $m->name }} ({{ $m->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">الصنف / المنتج الجاري تشغيله *</label>
                            <button type="button" onclick="toggleCustomProductMode()" class="text-[11px] text-red-600 hover:text-red-800 font-bold flex items-center gap-1 transition">
                                <i class="fa-solid fa-plus-circle"></i>
                                <span id="customProdToggleText">+ صنف يدوي ورفع صورة</span>
                            </button>
                        </div>
                        <select name="product_id" id="report_product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-red-600 outline-none">
                            <option value="">-- اختر الصنف لمعاينة الصورة والمحتويات --</option>
                            <option value="custom" class="bg-red-50 text-red-700 font-black">➕ صنف يدوي جديد بالمصنع (كتابة يدوي ورفع صورة)</option>
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

                    <!-- Custom Product Input Panel -->
                    <div id="custom_product_panel" class="hidden sm:col-span-2 p-3.5 bg-red-50/70 border border-red-200 rounded-2xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-red-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-pen-to-square text-red-600"></i>
                                تسجيل صنف جديد يدوياً بالمصنع ورفع صورته ومواصفاته:
                            </span>
                            <button type="button" onclick="cancelCustomProductMode()" class="text-[11px] text-slate-500 hover:text-slate-800 font-bold">
                                إلغاء والعودة للقائمة
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">اسم الصنف / الكوڤير الجديد *</label>
                                <input type="text" name="custom_product_name" id="custom_product_name" placeholder="مثال: كوڤير 3 في 1 كبابجي درويش..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-bold focus:ring-1 focus:ring-red-600 outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">محتويات ومواصفات الكوڤير</label>
                                <input type="text" name="custom_product_components" id="custom_product_components" placeholder="مثال: معلقة إنجليزي + منديل جاف + خلة أسنان" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">رفع صورة تعريفية لمحتويات الكوڤير / الصنف (مثل صورة أبو طارق):</label>
                                <div class="flex items-center gap-3">
                                    <input type="file" name="custom_product_image" id="custom_product_image" accept="image/*" onchange="previewCustomImage(this)" class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer">
                                    <img id="custom_image_preview_img" src="" class="hidden w-14 h-14 object-cover rounded-xl border border-slate-300 shadow-sm flex-shrink-0">
                                </div>
                            </div>
                        </div>
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
                        <input type="text" name="operator_name" value="{{ Auth::user()->name }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none font-bold">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">تاريخ الوردية *</label>
                            <span class="text-[10px] text-red-600 font-semibold">متاح اليوم أو بأثر رجعي</span>
                        </div>
                        <input type="date" name="report_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none font-bold">
                    </div>

                    <!-- Flexible Hour Slot Input (Dropdown OR Free Custom Slot) -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">الفترة الزمنية (الساعة) *</label>
                            <button type="button" onclick="toggleCustomHourSlot()" id="btn_toggle_custom_hour" class="text-[11px] text-blue-600 hover:text-blue-800 font-black flex items-center gap-1 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span id="custom_hour_label_btn">كتابة ساعة حرة يدوياً (وقت مخصص)</span>
                            </button>
                        </div>

                        <!-- Dropdown Mode (Default) -->
                        <div id="hour_dropdown_wrapper">
                            <select name="hour_slot" id="report_hour_slot" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-red-600 outline-none">
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>

                        <!-- Custom Free Input Mode -->
                        <div id="hour_custom_wrapper" class="hidden space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-0.5">من وقت:</label>
                                    <input type="time" id="free_time_from" value="07:00" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold" onchange="updateFreeHourSlotText()">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-0.5">إلى وقت:</label>
                                    <input type="time" id="free_time_to" value="08:00" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold" onchange="updateFreeHourSlotText()">
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="custom_hour_slot_text" id="custom_hour_slot_text" placeholder="مثال: 07:00 ص - 08:00 ص" class="flex-1 px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold">
                                <span class="text-[10px] text-slate-400">حر بالكامل</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عدد القطع المنتجة في هذه الساعة *</label>
                        <input type="number" name="units_produced" id="report_units_produced" value="2250" placeholder="مثال: 2250" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-red-600 outline-none font-mono">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">سرعة الماكينة (قطعة / دقيقة) *</label>
                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded">بالقطعة</span>
                        </div>
                        <input type="number" step="0.1" name="belt_speed_m_min" id="report_speed" value="45.0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-blue-700 focus:ring-1 focus:ring-red-600 outline-none font-mono" placeholder="مثال: 45">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">كفاءة الماكينة OEE (%) *</label>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i class="fa-solid fa-calculator text-[9px]"></i>
                                <span>معادلة تلقائية</span>
                            </span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.01" name="efficiency_rate" id="report_efficiency" value="83.33" readonly required class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-black text-emerald-700 focus:outline-none cursor-not-allowed font-mono">
                            <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">%</span>
                        </div>
                    </div>

                    <!-- Live Breakdown of Equation (معادلة إدارة التشغيل) -->
                    <div class="sm:col-span-2 bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-xs text-slate-900 space-y-2">
                        <div class="flex items-center justify-between font-bold border-b border-slate-200 pb-1.5">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-square-root-variable text-red-600"></i>
                                <span>المعادلة المعتمدة لكفاءة الماكينة:</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-600 dir-ltr">(الإنتاج الفعلي ÷ الطاقة النظرية) × 100</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5 text-center pt-0.5">
                            <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-xs">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الطاقة النظرية (السرعة × 60)</span>
                                <b id="calc_theoretical" class="text-blue-700 font-mono text-xs font-black">2,700 ق/س</b>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-xs">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الإنتاج الفعلي بالساعة</span>
                                <b id="calc_actual" class="text-slate-800 font-mono text-xs font-black">2,250 قطعة</b>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-xs">
                                <span class="text-[10px] text-slate-500 block mb-0.5">الكفاءة المحققة (OEE)</span>
                                <b id="calc_eff_result" class="text-emerald-600 font-mono text-xs font-black">83.33%</b>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">عدد القطع التالفة / الهادر</label>
                        <input type="number" name="waste_units" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">مدة التوقف (بالدقائق إن وجدت)</label>
                        <input type="number" name="downtime_minutes" value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">سبب التوقف (اتركه فارغاً إذا كان تشغيلاً مستمراً بدون أعطال)</label>
                    <input type="text" name="downtime_reason" placeholder="مثال: تغيير بكرة سيلوفان، نظافة فكوك لحام..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الإجراءات المتخذة لمعالجة المشكلة</label>
                    <input type="text" name="actions_taken" placeholder="تمت إعادة ضبط السكينة واستئناف العمل فوراً..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-red-600 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('newReportModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-xl shadow transition">
                        حفظ التقرير ورفعه للإدارة العليا
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

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

<!-- Edit Hourly Report Modal -->
<div id="editReportModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل بيانات ساعة التشغيل</span>
                </h3>
                <p id="edit_machine_title" class="text-xs font-bold text-slate-500 mt-1"></p>
            </div>
            <button type="button" onclick="closeEditReportModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editReportForm" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الوردية *</label>
                    <select name="shift" id="edit_shift" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        <option value="1">☀️ الوردية الأولى (الصباحية)</option>
                        <option value="2">🌆 الوردية الثانية (المسائية)</option>
                        <option value="3">🌙 الوردية الثالثة (الليلية)</option>
                        <option value="custom">⚡ وردية مخصصة / إضافية</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الساعة الزمنية *</label>
                    <input type="text" name="hour_slot" id="edit_hour_slot" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم الفني / المشغل المسجل *</label>
                    <input type="text" name="operator_name" id="edit_operator_name" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الصنف / المنتج الجاري تشغيله</label>
                    <select name="product_id" id="edit_product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none">
                        <option value="">-- اختر الصنف --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">عدد القطع المنتجة في هذه الساعة *</label>
                    <input type="number" step="1" name="units_produced" id="edit_units_produced" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:ring-1 focus:ring-blue-600 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">سرعة الماكينة (قطعة / دقيقة) *</label>
                    <input type="number" step="0.1" name="belt_speed_m_min" id="edit_belt_speed" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-blue-700 focus:ring-1 focus:ring-blue-600 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الهادر التالف (قطعة)</label>
                    <input type="number" step="1" name="waste_units" id="edit_waste_units" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مدة التوقف (بالدقائق إن وجدت)</label>
                    <input type="number" step="1" name="downtime_minutes" id="edit_downtime_minutes" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-1 focus:ring-blue-600 outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">سبب التوقف (إن وجد)</label>
                <input type="text" name="downtime_reason" id="edit_downtime_reason" placeholder="مثال: تغيير بكرة سيلوفان، نظافة فكوك لحام، عطل هواء..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">الإجراءات المتخذة وملاحظات التشغيل</label>
                <textarea name="actions_taken" id="edit_actions_taken" rows="2" placeholder="ملاحظات فني التشغيل أو الإجراء الذي تم لتلافي التوقف..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-blue-600 outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditReportModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50 transition">إلغاء</button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ التعديلات وإعادة احتساب الكفاءة</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // 24-Hour and Extended Shift Hour Presets
    const SHIFT_HOURS_DATA = {
        '8': {
            '1': [
                { value: '08:00 - 09:00', label: '08:00 ص - 09:00 ص' },
                { value: '09:00 - 10:00', label: '09:00 ص - 10:00 ص' },
                { value: '10:00 - 11:00', label: '10:00 ص - 11:00 ص' },
                { value: '11:00 - 12:00', label: '11:00 ص - 12:00 م' },
                { value: '12:00 - 13:00', label: '12:00 م - 01:00 م' },
                { value: '13:00 - 14:00', label: '01:00 م - 02:00 م' },
                { value: '14:00 - 15:00', label: '02:00 م - 03:00 م' },
                { value: '15:00 - 16:00', label: '03:00 م - 04:00 م' }
            ],
            '2': [
                { value: '16:00 - 17:00', label: '04:00 م - 05:00 م' },
                { value: '17:00 - 18:00', label: '05:00 م - 06:00 م' },
                { value: '18:00 - 19:00', label: '06:00 م - 07:00 م' },
                { value: '19:00 - 20:00', label: '07:00 م - 08:00 م' },
                { value: '20:00 - 21:00', label: '08:00 م - 09:00 م' },
                { value: '21:00 - 22:00', label: '09:00 م - 10:00 م' },
                { value: '22:00 - 23:00', label: '10:00 م - 11:00 م' },
                { value: '23:00 - 00:00', label: '11:00 م - 12:00 ص' }
            ],
            '3': [
                { value: '00:00 - 01:00', label: '12:00 ص - 01:00 ص' },
                { value: '01:00 - 02:00', label: '01:00 ص - 02:00 ص' },
                { value: '02:00 - 03:00', label: '02:00 ص - 03:00 ص' },
                { value: '03:00 - 04:00', label: '03:00 ص - 04:00 ص' },
                { value: '04:00 - 05:00', label: '04:00 ص - 05:00 ص' },
                { value: '05:00 - 06:00', label: '05:00 ص - 06:00 ص' },
                { value: '06:00 - 07:00', label: '06:00 ص - 07:00 ص' },
                { value: '07:00 - 08:00', label: '07:00 ص - 08:00 ص' }
            ],
            'custom': [
                { value: '07:00 - 08:00', label: '07:00 ص - 08:00 ص' },
                { value: '08:00 - 09:00', label: '08:00 ص - 09:00 ص' },
                { value: '09:00 - 10:00', label: '09:00 ص - 10:00 ص' },
                { value: '10:00 - 11:00', label: '10:00 ص - 11:00 ص' },
                { value: '11:00 - 12:00', label: '11:00 ص - 12:00 م' },
                { value: '12:00 - 13:00', label: '12:00 م - 01:00 م' },
                { value: '13:00 - 14:00', label: '01:00 م - 02:00 م' },
                { value: '14:00 - 15:00', label: '02:00 م - 03:00 م' },
                { value: '15:00 - 16:00', label: '03:00 م - 04:00 م' },
                { value: '16:00 - 17:00', label: '04:00 م - 05:00 م' },
                { value: '17:00 - 18:00', label: '05:00 م - 06:00 م' },
                { value: '18:00 - 19:00', label: '06:00 م - 07:00 م' }
            ]
        },
        '10': [
            { value: '07:00 - 08:00', label: '07:00 ص - 08:00 ص (ساعة 1)' },
            { value: '08:00 - 09:00', label: '08:00 ص - 09:00 ص (ساعة 2)' },
            { value: '09:00 - 10:00', label: '09:00 ص - 10:00 ص (ساعة 3)' },
            { value: '10:00 - 11:00', label: '10:00 ص - 11:00 ص (ساعة 4)' },
            { value: '11:00 - 12:00', label: '11:00 ص - 12:00 م (ساعة 5)' },
            { value: '12:00 - 13:00', label: '12:00 م - 01:00 م (ساعة 6)' },
            { value: '13:00 - 14:00', label: '01:00 م - 02:00 م (ساعة 7)' },
            { value: '14:00 - 15:00', label: '02:00 م - 03:00 م (ساعة 8)' },
            { value: '15:00 - 16:00', label: '03:00 م - 04:00 م (ساعة 9)' },
            { value: '16:00 - 17:00', label: '04:00 م - 05:00 م (ساعة 10)' }
        ],
        '12': [
            { value: '07:00 - 08:00', label: '07:00 ص - 08:00 ص (ساعة 1)' },
            { value: '08:00 - 09:00', label: '08:00 ص - 09:00 ص (ساعة 2)' },
            { value: '09:00 - 10:00', label: '09:00 ص - 10:00 ص (ساعة 3)' },
            { value: '10:00 - 11:00', label: '10:00 ص - 11:00 ص (ساعة 4)' },
            { value: '11:00 - 12:00', label: '11:00 ص - 12:00 م (ساعة 5)' },
            { value: '12:00 - 13:00', label: '12:00 م - 01:00 م (ساعة 6)' },
            { value: '13:00 - 14:00', label: '01:00 م - 02:00 م (ساعة 7)' },
            { value: '14:00 - 15:00', label: '02:00 م - 03:00 م (ساعة 8)' },
            { value: '15:00 - 16:00', label: '03:00 م - 04:00 م (ساعة 9)' },
            { value: '16:00 - 17:00', label: '04:00 م - 05:00 م (ساعة 10)' },
            { value: '17:00 - 18:00', label: '05:00 م - 06:00 م (ساعة 11)' },
            { value: '18:00 - 19:00', label: '06:00 م - 07:00 م (ساعة 12)' }
        ]
    };

    let currentDurationMode = '8';
    let currentShift = '1';
    let isCustomHourActive = false;

    // Switch View Modes: 'grid' (Excel table) vs 'analytics' (Machine Analytics) vs 'cards' (Cards)
    function switchViewMode(mode) {
        document.getElementById('view_grid_container').classList.add('hidden');
        document.getElementById('view_analytics_container').classList.add('hidden');
        document.getElementById('view_cards_container').classList.add('hidden');

        document.getElementById('tab_btn_grid').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-50 text-slate-600 hover:bg-slate-100';
        document.getElementById('tab_btn_analytics').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-50 text-slate-600 hover:bg-slate-100';
        document.getElementById('tab_btn_cards').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-50 text-slate-600 hover:bg-slate-100';

        if (mode === 'grid') {
            document.getElementById('view_grid_container').classList.remove('hidden');
            document.getElementById('tab_btn_grid').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-900 text-white shadow-xs';
        } else if (mode === 'analytics') {
            document.getElementById('view_analytics_container').classList.remove('hidden');
            document.getElementById('tab_btn_analytics').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-900 text-white shadow-xs';
        } else {
            document.getElementById('view_cards_container').classList.remove('hidden');
            document.getElementById('tab_btn_cards').className = 'view-tab-btn px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 bg-slate-900 text-white shadow-xs';
        }

        const filterViewInput = document.getElementById('filter_view_input');
        if (filterViewInput) filterViewInput.value = mode;
    }

    function setShiftInModal(shiftVal) {
        currentShift = shiftVal;
        document.getElementById('report_shift_input').value = shiftVal;

        ['1', '2', '3', 'custom'].forEach(s => {
            const btn = document.getElementById('modal_shift_btn_' + s);
            const check = document.getElementById('modal_shift_check_' + s);
            if (!btn || !check) return;

            if (s === shiftVal) {
                if (s === '1') btn.className = 'shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-amber-500 bg-amber-50 ring-2 ring-amber-400/30';
                else if (s === '2') btn.className = 'shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-blue-500 bg-blue-50 ring-2 ring-blue-400/30';
                else if (s === '3') btn.className = 'shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-indigo-500 bg-indigo-50 ring-2 ring-indigo-400/30';
                else btn.className = 'shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-emerald-500 bg-emerald-50 ring-2 ring-emerald-400/30';
                check.classList.remove('hidden');
            } else {
                btn.className = 'shift-selector-btn p-2.5 rounded-xl border-2 text-right transition flex items-center justify-between border-slate-200 bg-white hover:border-slate-300';
                check.classList.add('hidden');
            }
        });

        populateModalHours();
    }

    function setShiftHoursMode(mode) {
        currentDurationMode = mode;
        ['8', '10', '12'].forEach(d => {
            const btn = document.getElementById('dur_btn_' + d);
            if (!btn) return;
            if (d === mode) {
                btn.className = 'px-2.5 py-1 rounded-lg text-[11px] font-black bg-red-600 text-white transition';
            } else {
                btn.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-100 transition';
            }
        });

        toggleCustomHourSlot(false);
        populateModalHours();
    }

    function populateModalHours() {
        const select = document.getElementById('report_hour_slot');
        if (!select) return;
        select.innerHTML = '';

        let slots = [];
        if (currentDurationMode === '8') {
            slots = (SHIFT_HOURS_DATA['8'] && SHIFT_HOURS_DATA['8'][currentShift]) ? SHIFT_HOURS_DATA['8'][currentShift] : SHIFT_HOURS_DATA['8']['1'];
        } else {
            slots = SHIFT_HOURS_DATA[currentDurationMode] || SHIFT_HOURS_DATA['8']['1'];
        }

        slots.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.value;
            opt.textContent = s.label;
            select.appendChild(opt);
        });
    }

    function toggleCustomHourSlot(forceCustom = null) {
        if (forceCustom !== null) {
            isCustomHourActive = forceCustom;
        } else {
            isCustomHourActive = !isCustomHourActive;
        }

        const dropWrapper = document.getElementById('hour_dropdown_wrapper');
        const customWrapper = document.getElementById('hour_custom_wrapper');
        const labelBtn = document.getElementById('custom_hour_label_btn');
        const dropSelect = document.getElementById('report_hour_slot');
        const customTextInput = document.getElementById('custom_hour_slot_text');

        if (isCustomHourActive) {
            dropWrapper.classList.add('hidden');
            customWrapper.classList.remove('hidden');
            labelBtn.textContent = 'العودة لاختيار الساعات المحددة';
            updateFreeHourSlotText();
            if (dropSelect) dropSelect.name = 'unused_hour_slot';
            if (customTextInput) {
                customTextInput.name = 'hour_slot';
                customTextInput.focus();
            }
        } else {
            dropWrapper.classList.remove('hidden');
            customWrapper.classList.add('hidden');
            labelBtn.textContent = 'كتابة ساعة حرة يدوياً (وقت مخصص)';
            if (dropSelect) dropSelect.name = 'hour_slot';
            if (customTextInput) customTextInput.name = 'custom_hour_slot_text';
        }
    }

    function updateFreeHourSlotText() {
        const from = document.getElementById('free_time_from')?.value || '07:00';
        const to = document.getElementById('free_time_to')?.value || '08:00';
        const txtInput = document.getElementById('custom_hour_slot_text');
        if (txtInput) {
            txtInput.value = `${from} - ${to}`;
        }
    }

    function calculateOEE() {
        const speedInput = document.getElementById('report_speed');
        const unitsInput = document.getElementById('report_units_produced');
        const effInput = document.getElementById('report_efficiency');
        
        const speed = parseFloat(speedInput ? speedInput.value : 0) || 0;
        const units = parseFloat(unitsInput ? unitsInput.value : 0) || 0;
        
        const theoreticalHour = speed * 60;
        let efficiency = 0;
        if (theoreticalHour > 0) {
            efficiency = (units / theoreticalHour) * 100;
        }
        
        const formattedEff = efficiency.toFixed(2);
        if (effInput) effInput.value = formattedEff;
        
        const calcTheo = document.getElementById('calc_theoretical');
        const calcAct = document.getElementById('calc_actual');
        const calcRes = document.getElementById('calc_eff_result');
        
        if (calcTheo) calcTheo.innerText = theoreticalHour.toLocaleString('en-US') + ' ق/س';
        if (calcAct) calcAct.innerText = units.toLocaleString('en-US') + ' قطعة';
        if (calcRes) {
            calcRes.innerText = formattedEff + '%';
            if (efficiency >= 90) calcRes.className = 'text-emerald-600 font-mono text-xs font-black';
            else if (efficiency >= 75) calcRes.className = 'text-amber-600 font-mono text-xs font-black';
            else calcRes.className = 'text-rose-600 font-mono text-xs font-black';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto select current shift based on current hour
        const curHour = new Date().getHours();
        let defaultShift = '1';
        if (curHour >= 16 && curHour < 24) defaultShift = '2';
        else if (curHour >= 0 && curHour < 8) defaultShift = '3';
        setShiftInModal(defaultShift);

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
            const panel = document.getElementById('custom_product_panel');
            const img = document.getElementById('preview_prod_img');
            const name = document.getElementById('preview_prod_name');
            const compContainer = document.getElementById('preview_prod_components');

            if (!productSelect || !productSelect.value) {
                if (previewBox) previewBox.classList.add('hidden');
                if (panel) panel.classList.add('hidden');
                return;
            }

            if (productSelect.value === 'custom') {
                if (panel) panel.classList.remove('hidden');
                if (previewBox) previewBox.classList.add('hidden');
                document.getElementById('custom_product_name')?.focus();
                return;
            } else {
                if (panel) panel.classList.add('hidden');
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

    function toggleCustomProductMode() {
        const panel = document.getElementById('custom_product_panel');
        const previewBox = document.getElementById('product_preview_box');
        const productSelect = document.getElementById('report_product_id');
        if (!panel) return;

        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            if (previewBox) previewBox.classList.add('hidden');
            if (productSelect) productSelect.value = 'custom';
            document.getElementById('custom_product_name')?.focus();
        } else {
            cancelCustomProductMode();
        }
    }

    function cancelCustomProductMode() {
        const panel = document.getElementById('custom_product_panel');
        const previewBox = document.getElementById('product_preview_box');
        const productSelect = document.getElementById('report_product_id');

        if (panel) panel.classList.add('hidden');
        if (productSelect && productSelect.value === 'custom') {
            productSelect.selectedIndex = productSelect.options.length > 2 ? 2 : 0;
        }
        if (previewBox) previewBox.classList.remove('hidden');
        const opt = productSelect ? productSelect.options[productSelect.selectedIndex] : null;
        if (opt && opt.getAttribute('data-name')) {
            document.getElementById('preview_prod_name').innerText = opt.getAttribute('data-name');
            document.getElementById('preview_prod_img').src = opt.getAttribute('data-image');
        }
    }

    function previewCustomImage(input) {
        const preview = document.getElementById('custom_image_preview_img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

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

    // Edit Hourly Report Modal
    function openEditReportModal(rep) {
        if (!rep) return;
        const modal = document.getElementById('editReportModal');
        const form = document.getElementById('editReportForm');
        
        form.action = `{{ url('operations/hourly-reports') }}/${rep.id}/update`;
        
        document.getElementById('edit_machine_title').textContent = `${rep.machine_name} (${rep.machine_code}) — وردية ${rep.shift || '1'} (${rep.report_date})`;
        document.getElementById('edit_operator_name').value = rep.operator_name || '';
        document.getElementById('edit_units_produced').value = rep.units_produced || '';
        document.getElementById('edit_belt_speed').value = rep.belt_speed_m_min || '';
        document.getElementById('edit_waste_units').value = rep.waste_units || 0;
        document.getElementById('edit_downtime_minutes').value = rep.downtime_minutes || 0;
        document.getElementById('edit_downtime_reason').value = rep.downtime_reason || '';
        document.getElementById('edit_actions_taken').value = rep.actions_taken || '';
        document.getElementById('edit_hour_slot').value = rep.hour_slot || '';

        const shiftSelect = document.getElementById('edit_shift');
        if (shiftSelect) {
            shiftSelect.value = rep.shift || '1';
        }

        const prodSelect = document.getElementById('edit_product_id');
        if (prodSelect) {
            prodSelect.value = rep.product_id || '';
        }

        modal.classList.remove('hidden');
    }

    function closeEditReportModal() {
        const modal = document.getElementById('editReportModal');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
