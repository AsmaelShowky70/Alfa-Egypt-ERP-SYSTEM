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

    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center py-6">

        <!-- Brand & System Overview -->
        <div class="lg:col-span-6 space-y-6 text-white">
            <div class="inline-flex items-center gap-2 bg-red-600/10 text-red-400 border border-red-500/30 px-3.5 py-1.5 rounded-full text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                بيئة التشغيل الفعلية - مصانع ألفا إيجبت
            </div>

            <div class="flex items-center gap-3">
                <img src="{{ asset('images/alpha_symbol_nav.png') }}" alt="Alfa Symbol" class="h-14 w-auto object-contain hidden sm:block">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                        شركة الفا ايجبت للصناعات المتطورة
                    </h1>
                    <p class="text-xs font-bold text-red-500 tracking-wider mt-1">ALPHA EGYPT FOR MODERN PACKAGING INDUSTRIES</p>
                </div>
            </div>

            <p class="text-zinc-300 text-sm leading-relaxed">
                منظومة ERP المتكاملة لإدارة دورة العمل الكاملة: المبيعات والعملاء، أوامر التشغيل والإنتاج، المخازن المركزية والجرد بالصور، المشتريات والتوريد الداخلي (+14%)، مراكز ربحية المصانع الـ 6، الحسابات المالية، ومتابعة الماكينات لحظة بلحظة.
            </p>

            <div class="grid grid-cols-2 gap-3 pt-2 text-xs">
                <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="font-bold text-zinc-100">دخول آمن ومخصص</div>
                        <div class="text-[11px] text-zinc-400">صلاحيات مستقلة لكل إدارة</div>
                    </div>
                </div>
                <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-industry"></i>
                    </div>
                    <div>
                        <div class="font-bold text-zinc-100">6 مصانع متكاملة</div>
                        <div class="text-[11px] text-zinc-400">تسعير +14% ومراقبة ربحية</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Form Box -->
        <div class="lg:col-span-6">
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-t-4 border-red-600 border-x border-b border-zinc-100 space-y-6 relative overflow-hidden">
                <div class="text-center space-y-2">
                    <div class="flex justify-center">
                        <img src="{{ asset('images/alpha_logo_login.png') }}" alt="Alfa Egypt" class="h-28 w-auto object-contain">
                    </div>
                    <h2 class="text-xl font-black text-zinc-950 pt-1">تسجيل الدخول للنظام</h2>
                    <p class="text-xs text-zinc-500 font-medium">أدخل البريد الإلكتروني وكلمة المرور الخاصة بحسابك</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border-r-4 border-red-600 p-3 rounded-xl text-xs text-red-800 font-semibold space-y-1">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-r-4 border-red-600 p-3 rounded-xl text-xs text-red-800 font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-50 border-r-4 border-blue-600 p-3 rounded-xl text-xs text-blue-800 font-semibold">
                        {{ session('info') }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">البريد الإلكتروني</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute right-3 top-3.5 text-zinc-400 text-sm"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full pr-10 pl-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 focus:bg-white outline-none transition" placeholder="name@alphaegypt.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">كلمة المرور</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute right-3 top-3.5 text-zinc-400 text-sm"></i>
                            <input type="password" name="password" required autocomplete="current-password" class="w-full pr-10 pl-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 focus:bg-white outline-none transition" placeholder="••••••••">
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
