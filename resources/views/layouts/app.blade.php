<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'الرئيسية') - الفا ايجبت ERP للتعبئة والتغليف</title>

    <!-- Google Fonts: Cairo & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN with plugins -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                            950: '#450a0a',
                        },
                        dark: {
                            800: '#18181b',
                            900: '#0f172a',
                            950: '#000000',
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'Tajawal', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
        .print-only {
            display: none;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col bg-slate-50">

    <!-- TOP HEADER -->
    <header class="bg-black text-white shadow-xl sticky top-0 z-40 border-b-2 border-red-600 no-print">
        <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <!-- Brand & Toggle -->
            <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="text-slate-300 hover:text-white lg:hidden p-2 rounded-lg hover:bg-neutral-800">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/alpha_symbol_nav.png') }}" alt="الفا ايجبت" class="h-11 w-auto drop-shadow-[0_2px_8px_rgba(220,38,38,0.5)] group-hover:scale-105 transition">
                    <div>
                        <div class="font-black text-lg leading-tight tracking-wide flex items-center gap-2">
                            <span class="text-white">الفا ايجبت</span>
                            <span class="text-[10px] bg-red-600/30 text-red-400 border border-red-500/50 px-2 py-0.5 rounded-full font-bold">ERP 2.0</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium">لصناعة مواد التغليف الحديث والكوڤير المتكامل</div>
                    </div>
                </a>
            </div>

            <!-- Role Badge, Fast Switcher & User Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Loss Alarm Quick Counter if any -->
                @php
                    $headerLossAlarms = \App\Models\SalesOrder::where('has_loss_alarm', true)->whereNotIn('status', ['completed', 'cancelled'])->count();
                @endphp
                @if($headerLossAlarms > 0)
                    <a href="{{ route('finance.lossAlarms') }}" class="flex items-center gap-1.5 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow animate-pulse hover:bg-red-700 transition" title="إنذار: توجد عقود/طلبات تباع بسعر أقل من التكلفة!">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ $headerLossAlarms }} إنذار خسارة</span>
                    </a>
                @endif

                <!-- Fast Role Switcher Dropdown (for quick testing of any department) -->
                <div class="relative" id="fastSwitcherDropdown">
                    <button id="fastSwitcherBtn" type="button" class="flex items-center gap-2 bg-neutral-900 hover:bg-neutral-800 text-xs px-3 py-1.5 rounded-lg border border-neutral-700 text-slate-200 transition cursor-pointer select-none">
                        <i class="fa-solid fa-repeat text-red-500"></i>
                        <span class="hidden md:inline">التحويل السريع للدور:</span>
                        <span class="font-bold text-white">{{ Auth::user()->role_arabic_name }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div id="fastSwitcherMenu" class="dropdown-menu-custom absolute left-0 top-full pt-1.5 w-64 hidden z-50 text-slate-800">
                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 py-2">
                            <div class="px-3 py-1 text-[11px] font-bold text-slate-400 border-b border-slate-100 mb-1">
                                تبديل حساب المستخدم (تجربة فورية)
                            </div>
                            @php
                                $allSystemUsers = \App\Models\User::where('status', true)->get();
                            @endphp
                            @foreach($allSystemUsers as $u)
                                <a href="{{ route('login.fast', $u->id) }}" class="w-full text-right px-3 py-2 text-xs flex items-center justify-between hover:bg-red-50 transition {{ $u->id === Auth::id() ? 'bg-red-50 font-bold text-red-700' : 'text-slate-700' }}">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $u->avatar_url }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                        <div>
                                            <div class="leading-tight">{{ $u->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $u->job_title }}</div>
                                        </div>
                                    </div>
                                    @if($u->id === Auth::id())
                                        <i class="fa-solid fa-check text-red-600 text-xs"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="relative" id="userProfileDropdown">
                    <button id="userProfileBtn" type="button" class="flex items-center gap-2 text-right p-1.5 rounded-xl hover:bg-neutral-800 transition cursor-pointer select-none">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover border-2 border-red-600 shadow">
                        <div class="hidden sm:block text-right">
                            <div class="text-xs font-bold text-white leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-red-400 font-medium">{{ Auth::user()->job_title }}</div>
                        </div>
                        <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm hidden sm:block"></i>
                    </button>
                    <div id="userProfileMenu" class="dropdown-menu-custom absolute left-0 top-full pt-1.5 w-52 hidden z-50 text-slate-700">
                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 py-2">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs text-slate-400">مسجل كـ</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                                <span class="inline-block mt-1 text-[10px] bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-semibold">{{ Auth::user()->role_arabic_name }}</span>
                            </div>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-xs hover:bg-slate-50 transition flex items-center gap-2">
                                <i class="fa-solid fa-id-card text-red-600"></i>
                                <span>الملف الشخصي وكلمة المرور</span>
                            </a>
                            @if(Auth::user()->isOwner() || Auth::user()->isGM())
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-xs hover:bg-slate-50 transition flex items-center gap-2">
                                <i class="fa-solid fa-users-gear text-slate-700"></i>
                                <span>إدارة المستخدمين والصلاحيات</span>
                            </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-100 mt-1">
                                @csrf
                                <button type="submit" class="w-full text-right px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition flex items-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex flex-row">
        <!-- SIDEBAR -->
        <aside id="sidebar" class="w-64 bg-white border-l border-slate-200 flex-shrink-0 flex flex-col justify-between hidden lg:flex no-print">
            <div class="p-4 space-y-6 overflow-y-auto max-h-[calc(100vh-4rem)]">

                <!-- Department Navigation Groups -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">لوحة التحكم</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-brand-50 text-brand-700 border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i class="fa-solid fa-gauge-high text-brand-600 text-base"></i>
                        <span>داشبورد {{ Auth::user()->role_arabic_name }}</span>
                    </a>
                </div>

                <!-- 1. المبيعات والعملاء -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isSales())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>المبيعات والعملاء</span>
                        <i class="fa-solid fa-bag-shopping text-emerald-500"></i>
                    </div>
                    <a href="{{ route('sales.customers') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('sales.customers*') ? 'bg-emerald-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-users text-emerald-600"></i>
                        <span>قائمة العملاء واللوجوهات</span>
                    </a>
                    <a href="{{ route('sales.orders') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('sales.orders*') ? 'bg-emerald-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                            <span>طلبات المبيعات</span>
                        </div>
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">
                            {{ \App\Models\SalesOrder::count() }}
                        </span>
                    </a>
                    <a href="{{ route('sales.orders.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100/80 transition">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>تسجيل طلب جديد لعميل</span>
                    </a>
                </div>
                @endif

                <!-- 2. التشغيل والإنتاج -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isOperations())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>إدارة التشغيل والإنتاج</span>
                        <i class="fa-solid fa-industry text-blue-500"></i>
                    </div>
                    <a href="{{ route('operations.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('operations.index') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-sliders text-blue-600"></i>
                        <span>تخطيط أوامر التشغيل</span>
                    </a>
                    <a href="{{ route('operations.workOrders') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('operations.workOrders*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-gears text-blue-600"></i>
                            <span>أوامر الشغل للخطوط</span>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded-full">
                            {{ \App\Models\ProductionOrder::whereIn('status', ['ready', 'in_progress'])->count() }}
                        </span>
                    </a>
                    <a href="{{ route('operations.requisitions') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('operations.requisitions*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                            <span>طلبات واستلام الخامات</span>
                        </div>
                        @php
                            $inTransit = \App\Models\MaterialRequisition::where('status', 'issued')->count();
                        @endphp
                        @if($inTransit > 0)
                            <span class="text-[11px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full animate-pulse">
                                {{ $inTransit }} جاهز للاستلام
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('operations.hourlyReports') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('operations.hourlyReports*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                        <span>تقارير الماكينات كل ساعة</span>
                    </a>
                </div>
                @endif

                <!-- 3. المخازن المركزية -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isWarehouse())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>المخازن المركزية</span>
                        <i class="fa-solid fa-warehouse text-amber-500"></i>
                    </div>
                    <a href="{{ route('warehouse.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.index') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                        <span>نظرة عامة على المخزن</span>
                    </a>
                    <a href="{{ route('warehouse.rawMaterials') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.rawMaterials') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-cubes-stacked text-amber-600"></i>
                        <span>المواد الخام ومستلزمات الكوڤير</span>
                    </a>
                    <a href="{{ route('warehouse.finishedGoods') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.finishedGoods') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-box-open text-amber-600"></i>
                        <span>المنتج التام (الكوڤير والأكياس)</span>
                    </a>
                    <a href="{{ route('warehouse.spareParts') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.spareParts') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-wrench text-amber-600"></i>
                        <span>قطع غيار الماكينات</span>
                    </a>
                    <a href="{{ route('warehouse.materialRequisitions') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.materialRequisitions*') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                            <span>طلبات صرف التشغيل</span>
                        </div>
                        <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full">
                            {{ \App\Models\MaterialRequisition::where('status', 'requested')->count() }}
                        </span>
                    </a>
                    <a href="{{ route('warehouse.vouchers') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.vouchers*') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-receipt text-amber-600"></i>
                        <span>سندات الاستلام والصرف (طباعة)</span>
                    </a>
                    <a href="{{ route('warehouse.dailyChecks') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.dailyChecks*') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-clipboard-list text-amber-600"></i>
                        <span>الجرد اليومي للمخزن</span>
                    </a>
                    <a href="{{ route('warehouse.reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.reports*') && !request()->routeIs('warehouse.reports.archived*') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-chart-pie text-amber-600"></i>
                        <span>تقارير المخازن وحركة الأصناف</span>
                    </a>
                    <a href="{{ route('warehouse.reports.archived') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('warehouse.reports.archived*') ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-box-archive text-amber-600"></i>
                        <span>أرشيف التقارير المعتمدة</span>
                    </a>
                </div>
                @endif

                <!-- 4. المشتريات والتوريدات -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isPurchasing())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>المشتريات وسلاسل الإمداد</span>
                        <i class="fa-solid fa-cart-flatbed text-purple-500"></i>
                    </div>
                    <a href="{{ route('purchasing.requisitions') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('purchasing.requisitions*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-bell-concierge text-purple-600"></i>
                            <span>طلبات النواقص من المخزن</span>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded-full">
                            {{ \App\Models\PurchaseRequisition::where('status', 'pending_procurement')->count() }}
                        </span>
                    </a>
                    <a href="{{ route('purchasing.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('purchasing.orders*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-truck-ramp-box text-purple-600"></i>
                        <span>أوامر الشراء (موردين + خطوط 14%)</span>
                    </a>
                    <a href="{{ route('purchasing.suppliers') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('purchasing.suppliers*') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-handshake text-purple-600"></i>
                        <span>سجل الموردين والتصنيع الخارجي</span>
                    </a>
                </div>
                @endif

                <!-- 5. الحسابات والمالية -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isFinance())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>المالية والتكاليف</span>
                        <i class="fa-solid fa-scale-balanced text-teal-500"></i>
                    </div>
                    <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('finance.index') ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-coins text-teal-600"></i>
                        <span>المصروفات والإيرادات والفواتير</span>
                    </a>
                    <a href="{{ route('finance.pricing') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('finance.pricing*') ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-tags text-teal-600"></i>
                        <span>محرك التسعير وتكاليف BOM</span>
                    </a>
                    <a href="{{ route('finance.lossAlarms') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('finance.lossAlarms*') ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                            <span>شاشة إنذارات الخسارة</span>
                        </div>
                        @if($headerLossAlarms > 0)
                        <span class="text-xs bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full">
                            {{ $headerLossAlarms }}
                        </span>
                        @endif
                    </a>
                </div>
                @endif

                <!-- 6. المصانع والخطوط المستقلة (مراكز ربح 14%) -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isOperations() || Auth::user()->isFinance())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>المصانع والخطوط المستقلة (6)</span>
                        <i class="fa-solid fa-microchip text-indigo-500"></i>
                    </div>
                    <a href="{{ route('factories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('factories*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-chart-pie text-indigo-600"></i>
                        <span>مراكز ربحية المصانع والـ 14%</span>
                    </a>
                </div>
                @endif

                <!-- 7. الصيانة والمعدات -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM() || Auth::user()->isMaintenance())
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>الصيانة الهندسية</span>
                        <i class="fa-solid fa-screwdriver-wrench text-orange-500"></i>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('maintenance*') ? 'bg-orange-50 text-orange-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-oil-can text-orange-600"></i>
                        <span>ماكينات الخطوط وجاهزيتها</span>
                    </a>
                </div>
                @endif

                <!-- 8. الإدارة العليا والمستخدمين -->
                @if(Auth::user()->isOwner() || Auth::user()->isGM())
                <div class="space-y-1 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <span>الإدارة العليا</span>
                        <i class="fa-solid fa-crown text-amber-500"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('users*') ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-user-shield text-slate-700"></i>
                        <span>إدارة المديرين والصلاحيات</span>
                    </a>
                </div>
                @endif

            </div>

            <!-- Footer info in sidebar -->
            <div class="p-4 border-t border-slate-200 text-xs text-slate-500 bg-slate-50 text-center">
                <img src="{{ asset('images/alpha_logo_nav.png') }}" class="h-8 mx-auto object-contain mb-1.5 drop-shadow-sm">
                <div class="font-black text-slate-800">شركة الفا ايجبت</div>
                <div class="text-[10px] text-slate-400">لصناعة مواد التغليف الحديث © {{ date('Y') }}</div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-r-4 border-brand-600 p-4 rounded-xl shadow-sm flex items-start gap-3 no-print">
                    <i class="fa-solid fa-circle-check text-brand-600 text-lg mt-0.5"></i>
                    <div class="text-sm font-semibold text-emerald-900">{!! session('success') !!}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-rose-50 border-r-4 border-rose-600 p-4 rounded-xl shadow-sm flex items-start gap-3 no-print">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5"></i>
                    <div class="text-sm font-semibold text-rose-900">{!! session('error') !!}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 bg-blue-50 border-r-4 border-blue-600 p-4 rounded-xl shadow-sm flex items-start gap-3 no-print">
                    <i class="fa-solid fa-circle-info text-blue-600 text-lg mt-0.5"></i>
                    <div class="text-sm font-semibold text-blue-900">{!! session('info') !!}</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Navigation & Dropdown Scripts -->
    <script>
        // Mobile Sidebar Toggle
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('fixed');
                sidebar.classList.toggle('inset-y-0');
                sidebar.classList.toggle('right-0');
                sidebar.classList.toggle('z-50');
            });
        }

        // Robust Dropdown Handler (Click to lock open + Smooth safe hover)
        function setupDropdown(wrapperId, triggerBtnId, menuId) {
            const wrapper = document.getElementById(wrapperId);
            const trigger = document.getElementById(triggerBtnId);
            const menu = document.getElementById(menuId);
            if (!wrapper || !trigger || !menu) return;

            let closeTimer = null;

            function openMenu() {
                if (closeTimer) clearTimeout(closeTimer);
                document.querySelectorAll('.dropdown-menu-custom').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });
                menu.classList.remove('hidden');
            }

            function closeMenu(delay = 0) {
                if (delay === 0) {
                    menu.classList.add('hidden');
                } else {
                    if (closeTimer) clearTimeout(closeTimer);
                    closeTimer = setTimeout(() => {
                        menu.classList.add('hidden');
                    }, delay);
                }
            }

            // Click toggle: Locks open until outside click or selecting an item
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (menu.classList.contains('hidden')) {
                    openMenu();
                } else {
                    closeMenu(0);
                }
            });

            // Smooth Hover: open on enter, 350ms grace period on leave
            wrapper.addEventListener('mouseenter', () => {
                openMenu();
            });

            wrapper.addEventListener('mouseleave', () => {
                closeMenu(350);
            });

            menu.addEventListener('mouseenter', () => {
                if (closeTimer) clearTimeout(closeTimer);
            });

            menu.addEventListener('mouseleave', () => {
                closeMenu(350);
            });
        }

        setupDropdown('userProfileDropdown', 'userProfileBtn', 'userProfileMenu');
        setupDropdown('fastSwitcherDropdown', 'fastSwitcherBtn', 'fastSwitcherMenu');

        // Click outside closes all dropdowns
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#userProfileDropdown') && !e.target.closest('#fastSwitcherDropdown')) {
                document.querySelectorAll('.dropdown-menu-custom').forEach(m => m.classList.add('hidden'));
            }
        });

        // ESC key closes all dropdowns
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu-custom').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
