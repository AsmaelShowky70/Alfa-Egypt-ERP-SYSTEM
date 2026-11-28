<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - الفا ايجبت ERP للتعبئة والتغليف</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-black via-zinc-950 to-neutral-900 min-h-screen text-slate-800 flex items-center justify-center p-4 selection:bg-red-600 selection:text-white">

    <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center py-6">

        <!-- Left / Top Info: Fast Role Selection Cards (دخول سريع وتجربة الأدوار) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="space-y-3 text-white">
                <div class="inline-flex items-center gap-2 bg-red-600/10 text-red-400 border border-red-500/30 px-3.5 py-1.5 rounded-full text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                    نظام ERP المتخصص في تصنيع الكوڤير والتعبئة والتغليف
                </div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/alpha_symbol_nav.png') }}" alt="Alfa Symbol" class="h-12 w-auto object-contain hidden sm:block">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                            شركة الفا ايجبت للصناعات المتطورة
                        </h1>
                        <p class="text-xs font-bold text-red-500 tracking-wider">ALPHA EGYPT FOR MODERN PACKAGING INDUSTRIES</p>
                    </div>
                </div>
                <p class="text-zinc-300 text-sm max-w-xl leading-relaxed">
                    منظومة متكاملة لربط دورة المبيعات مع أوامر التشغيل، المخازن المركزية، المشتريات، مراكز تكلفة المصانع الـ 6 المستقلة بهامش 14%، وتقارير الماكينات اللحظية.
                </p>
            </div>

            <!-- Fast Click Login Cards (الأدوار الفعلية للمديرين) -->
            <div class="bg-zinc-900/90 backdrop-blur-md border border-zinc-800 p-5 rounded-3xl shadow-2xl space-y-3">
                <div class="flex items-center justify-between text-white border-b border-zinc-800 pb-2">
                    <span class="text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-users text-red-500"></i>
                        الدخول السريع بحسابات الإدارة والمديرين (نقرة واحدة للتجربة):
                    </span>
                    <span class="text-[11px] text-red-300 bg-red-950/80 border border-red-800/50 px-2.5 py-0.5 rounded-full font-bold">{{ $users->count() }} حسابات مخصصة</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @foreach($users as $user)
                        <a href="{{ route('login.fast', $user->id) }}" class="w-full text-right bg-zinc-950/80 hover:bg-black text-white p-2.5 rounded-2xl border border-zinc-800 hover:border-red-600/80 shadow-sm hover:shadow-lg hover:shadow-red-950/40 transition flex items-center gap-3 group">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-11 h-11 rounded-xl object-cover border border-zinc-700 shadow-sm flex-shrink-0 group-hover:border-red-500 transition">
                            <div class="overflow-hidden flex-1">
                                <div class="font-extrabold text-xs text-zinc-100 truncate group-hover:text-red-400 transition">{{ $user->name }}</div>
                                <div class="text-[11px] font-semibold text-red-400 truncate">{{ $user->job_title }}</div>
                                <div class="text-[10px] text-zinc-400 truncate">{{ $user->department }}</div>
                            </div>
                            <i class="fa-solid fa-arrow-left text-xs text-zinc-600 group-hover:text-red-500 transition ml-1"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right / Bottom: Traditional Login Box -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-t-4 border-red-600 border-x border-b border-zinc-100 space-y-6 relative overflow-hidden">
                <div class="text-center space-y-2">
                    <div class="flex justify-center">
                        <img src="{{ asset('images/alpha_logo_login.png') }}" alt="Alfa Egypt" class="h-28 w-auto object-contain">
                    </div>
                    <h2 class="text-xl font-black text-zinc-950 pt-1">تسجيل الدخول للنظام</h2>
                    <p class="text-xs text-zinc-500 font-medium">أدخل البريد الإلكتروني وكلمة المرور الخاصة بك</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border-r-4 border-red-600 p-3 rounded-xl text-xs text-red-800 font-semibold space-y-1">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">البريد الإلكتروني</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute right-3 top-3.5 text-zinc-400 text-sm"></i>
                            <input type="email" name="email" value="{{ old('email', 'ezzat@alphaegypt.com') }}" required class="w-full pr-10 pl-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 focus:bg-white outline-none transition" placeholder="name@alphaegypt.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-zinc-700">كلمة المرور</label>
                            <span class="text-[11px] text-zinc-400 font-medium">(الافتراضي: secret123)</span>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute right-3 top-3.5 text-zinc-400 text-sm"></i>
                            <input type="password" name="password" value="secret123" required class="w-full pr-10 pl-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 focus:bg-white outline-none transition" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-zinc-600">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-red-600 focus:ring-red-500">
                            <span>تذكرني على هذا الجهاز</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-xl font-extrabold text-sm shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>دخول إلى لوحة التحكم</span>
                    </button>
                </form>

                <div class="pt-4 border-t border-zinc-100 text-center text-xs text-zinc-400">
                    نظام الفا ايجبت ERP - الإصدار المؤسسي 2.0
                </div>
            </div>
        </div>

    </div>

</body>
</html>
