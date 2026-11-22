@extends('layouts.app')

@section('title', 'سجل الموردين والتصنيع الخارجي')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-handshake text-purple-600"></i>
                <span>سجل الموردين وشركاء التصنيع عند الغير</span>
            </h1>
            <p class="text-xs text-slate-500">موردي خرز البلاستيك، الورق، وشركاء التصنيع الخارجي (حقن البلاستيك، رولات التغليف المطبوعة)</p>
        </div>
        <button onclick="document.getElementById('newSupplierModal').classList.toggle('hidden')" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إضافة مورد جديد</span>
        </button>
    </div>

    <!-- Suppliers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($suppliers as $s)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ str_contains($s->category, 'خارجي') ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $s->category }}
                            </span>
                            <h3 class="font-extrabold text-base text-slate-900 mt-1">{{ $s->name }}</h3>
                            <span class="font-mono text-xs font-bold text-slate-400">{{ $s->code }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs font-bold bg-amber-50 px-2 py-1 rounded-lg">
                            <i class="fa-solid fa-star"></i>
                            <span>{{ $s->rating }}</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                        @if($s->contact_person)
                            <div>المسؤول: <b>{{ $s->contact_person }}</b></div>
                        @endif
                        @if($s->phone)
                            <div dir="ltr" class="text-right">هاتف: {{ $s->phone }}</div>
                        @endif
                        @if($s->address)
                            <div class="truncate text-slate-500">{{ $s->address }}</div>
                        @endif
                        <div class="text-purple-700 font-bold">متوسط مدة التوريد: {{ $s->average_lead_time_days }} أيام</div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>أوامر الشراء: <b>{{ $s->purchase_orders_count }} أمر</b></span>
                    <a href="{{ route('purchasing.orders.create') }}" class="font-bold text-purple-600 hover:underline">
                        إصدار أمر توريد →
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal to Add New Supplier -->
    <div id="newSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-purple-600"></i>
                    <span>إضافة مورد / شريك تصنيع جديد</span>
                </h3>
                <button onclick="document.getElementById('newSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('purchasing.suppliers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم المورد / المصنع *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">كود المورد *</label>
                        <input type="text" name="code" value="SUP-{{ str_pad(\App\Models\Supplier::count() + 1, 3, '0', STR_PAD_LEFT) }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف</label>
                        <select name="category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="خامات">توريد خامات أولية</option>
                            <option value="تصنيع خارجي عند الغير">تصنيع خارجي عند الغير</option>
                            <option value="قطع غيار">قطع غيار ماكينات</option>
                            <option value="تعبئة وتغليف">مواد تعبئة وتغليف</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">مسؤول التواصل</label>
                        <input type="text" name="contact_person" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">متوسط مدة التوريد بالأيام *</label>
                    <input type="number" name="average_lead_time_days" value="3" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('newSupplierModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">حفظ المورد</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
