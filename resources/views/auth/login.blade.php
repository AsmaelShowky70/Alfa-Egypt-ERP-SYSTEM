<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - الفا ايجبت ERP للتعبئة والتغليف</title>
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
                            500: '#10b981',
                            600: '#008060',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
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
<body class="bg-gradient-to-br from-slate-900 via-brand-900 to-slate-900 min-h-screen text-slate-800 flex items-center justify-center p-4">

    <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

        <!-- Left / Top Info: Fast Role Selection Cards (دخول سريع وتجربة الأدوار) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="space-y-2 text-white">
                <div class="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-3 py-1 rounded-full text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    نظام ERP المتخصص في تصنيع الكوڤير والتعبئة والتغليف
                </div>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                    شركة الفا ايجبت للصناعات المتطورة
                </h1>
                <p class="text-emerald-100/80 text-sm max-w-xl leading-relaxed">
                    منظومة متكاملة لربط دورة المبيعات مع أوامر التشغيل، المخازن المركزية، المشتريات، مراكز تكلفة المصانع الـ 6 المستقلة بهامش 14%، وتقارير الماكينات اللحظية.
                </p>
            </div>

            <!-- Fast Click Login Cards (الأدوار الفعلية للمديرين) -->
            <div class="bg-white/10 backdrop-blur-md border border-white/10 p-5 rounded-3xl shadow-2xl space-y-3">
                <div class="flex items-center justify-between text-white border-b border-white/10 pb-2">
                    <span class="text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-users text-emerald-400"></i>
                        الدخول السريع بحسابات الإدارة والمديرين (نقرة واحدة للتجربة):
                    </span>
                    <span class="text-[11px] text-emerald-200 bg-brand-800/80 px-2.5 py-0.5 rounded-full font-semibold">8 أدوار مخصصة</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @foreach($users as $user)
                        <a href="{{ route('login.fast', $user->id) }}" class="w-full text-right bg-white/95 hover:bg-white text-slate-800 p-2.5 rounded-2xl shadow-sm hover:shadow-md hover:ring-2 hover:ring-emerald-400 transition flex items-center gap-3 group">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shadow-sm flex-shrink-0 group-hover:scale-105 transition">
                            <div class="overflow-hidden flex-1">
                                <div class="font-extrabold text-xs text-slate-900 truncate group-hover:text-brand-700 transition">{{ $user->name }}</div>
                                <div class="text-[11px] font-semibold text-emerald-700 truncate">{{ $user->job_title }}</div>
                                <div class="text-[10px] text-slate-400 truncate">{{ $user->department }}</div>
                            </div>
                            <i class="fa-solid fa-arrow-left text-xs text-slate-300 group-hover:text-brand-600 transition ml-1"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right / Bottom: Traditional Login Box -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-slate-100 space-y-6">
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-brand-700 to-emerald-400 flex items-center justify-center text-white shadow-lg text-2xl font-black">
                        α
                    </div>
                    <h2 class="text-xl font-black text-slate-900 pt-2">تسجيل الدخول للنظام</h2>
                    <p class="text-xs text-slate-500">أدخل البريد الإلكتروني وكلمة المرور الخاصة بك</p>
                </div>

                @if($errors->any())
                    <div class="bg-rose-50 border-r-4 border-rose-500 p-3 rounded-xl text-xs text-rose-800 font-semibold space-y-1">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute right-3 top-3.5 text-slate-400 text-sm"></i>
                            <input type="email" name="email" value="{{ old('email', 'ezzat@alphaegypt.com') }}" required class="w-full pr-10 pl-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-600 focus:bg-white outline-none transition" placeholder="name@alphaegypt.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">كلمة المرور</label>
                            <span class="text-[11px] text-slate-400 font-medium">(الافتراضي: secret123)</span>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute right-3 top-3.5 text-slate-400 text-sm"></i>
                            <input type="password" name="password" value="secret123" required class="w-full pr-10 pl-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-600 focus:bg-white outline-none transition" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500">
                            <span>تذكرني على هذا الجهاز</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-brand-600/30 hover:shadow-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>دخول إلى لوحة التحكم</span>
                    </button>
                </form>

                <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
                    نظام الفا ايجبت ERP - الإصدار المؤسسي 2.0
                </div>
            </div>
        </div>

    </div>

</body>
</html>
