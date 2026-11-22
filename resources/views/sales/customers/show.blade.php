@extends('layouts.app')

@section('title', 'ملف العميل: ' . $customer->name)

@section('content')
<div class="space-y-6">

    <!-- Customer Header Profile -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center gap-6 text-center sm:text-right">
            <img src="{{ $customer->logo_url }}" alt="{{ $customer->name }}" class="w-24 h-24 rounded-2xl object-contain bg-slate-50 p-2 border border-slate-200 shadow-md">
            <div class="space-y-1">
                <div class="flex items-center justify-center sm:justify-start gap-3">
                    <h1 class="text-2xl font-black text-slate-900">{{ $customer->name }}</h1>
                    <span class="text-xs bg-brand-100 text-brand-800 font-bold px-2.5 py-0.5 rounded-full">{{ $customer->code }}</span>
                </div>
                <p class="text-xs text-slate-500">{{ $customer->contact_person }} | {{ $customer->phone }} | {{ $customer->email }}</p>
                <p class="text-xs text-slate-600"><i class="fa-solid fa-location-dot text-slate-400"></i> {{ $customer->address ?? 'العنوان غير مسجل' }}</p>
                <div class="text-[11px] text-slate-400 pt-1">شروط الدفع: <b class="text-slate-700">{{ $customer->payment_terms }}</b></div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('sales.orders.create') }}?customer_id={{ $customer->id }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-cart-plus"></i>
                <span>تسجيل طلبية للعميل</span>
            </a>
            <a href="{{ route('sales.customers.edit', $customer->id) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تعديل البيانات</span>
            </a>
        </div>
    </div>

    <!-- Custom Products & Recipes (BOM) Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-brand-600"></i>
                    <span>المنتجات المخصصة للعميل (تركيبات الكوڤير والأكياس)</span>
                </h2>
                <p class="text-xs text-slate-500">مكونات كل طقم كوڤير (معلقة، منديل، خلة، وايبس) والتكلفة المحسوبة وسعر البيع التعاقدي</p>
            </div>
            <button onclick="document.getElementById('customProductModal').classList.toggle('hidden')" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-brand-700 font-bold text-xs rounded-xl transition border border-emerald-200 flex items-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة تركيبة كوڤير مخصصة</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($customer->products as $prod)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-mono text-[11px] font-bold text-slate-400">{{ $prod->code }}</span>
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $prod->name }}</h3>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $prod->has_negative_margin ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $prod->has_negative_margin ? 'خسارة' : 'هامش: ' . $prod->margin_percentage . '%' }}
                            </span>
                        </div>

                        <!-- BOM Ingredients List -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs space-y-1.5">
                            <span class="text-[10px] font-bold text-slate-400 block mb-1">المكونات والخامات في الطقم (BOM):</span>
                            @foreach($prod->boms as $bom)
                                <div class="flex items-center justify-between py-0.5 border-b border-slate-200/50 last:border-0">
                                    <span class="text-slate-700 font-medium">{{ $bom->rawMaterial->name ?? 'مكون' }}</span>
                                    <span class="text-slate-500 font-mono font-bold">{{ number_format($bom->unit_cost_snapshot, 2) }} ج.م</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="bg-slate-100/70 p-2 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-medium">التكلفة المباشرة</span>
                            <span class="font-black text-slate-800">{{ number_format($prod->cost_price, 2) }} ج.م</span>
                        </div>
                        <div class="bg-emerald-50 p-2 rounded-xl">
                            <span class="text-[10px] text-emerald-700 block font-medium">سعر البيع التعاقدي</span>
                            <span class="font-black text-emerald-900">{{ number_format($prod->selling_price, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white p-8 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs">
                    لم يتم تخصيص منتجات بعد لهذا العميل. اضغط على زر (إضافة تركيبة كوڤير مخصصة) بالأعلى لتحديد المكونات والتسعير.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal to Add Custom Product (BOM Builder) -->
    <div id="customProductModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-brand-600"></i>
                    <span>تخصيص تركيبة كوڤير جديدة للعميل (BOM Builder)</span>
                </h3>
                <button onclick="document.getElementById('customProductModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('sales.customers.custom_product', $customer->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم المنتج المخصص *</label>
                        <input type="text" name="name" placeholder="مثال: كوڤير 4*1 أزرق - كشري أبو طارق" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-brand-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">كود المنتج الفريد *</label>
                        <input type="text" name="code" value="PROD-CUST-{{ $customer->id }}-{{ time() % 1000 }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:bg-white focus:ring-1 focus:ring-brand-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف</label>
                        <select name="category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-brand-600 outline-none">
                            <option value="couvert">كوڤير معالق وأدوات مائدة</option>
                            <option value="wipes">مناديل معطرة ووايبس</option>
                            <option value="gloves">جوانتيات طعام واستخدام مطاعم</option>
                            <option value="packaging_roll">رولات وأكياس تعبئة</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">سعر البيع التعاقدي للعميل (ج.م) *</label>
                        <input type="number" step="0.01" name="selling_price" placeholder="مثال: 2.15" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-brand-700 focus:bg-white focus:ring-1 focus:ring-brand-600 outline-none">
                    </div>
                </div>

                <!-- Select Ingredients with real-time costs -->
                <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/60 space-y-3">
                    <span class="text-xs font-extrabold text-slate-800 block">حدد الخامات والمكونات المكونة لهذا الكوڤير (تجميع المقادير والتكلفة):</span>
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @foreach($rawMaterials as $idx => $rm)
                            <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200/80 text-xs">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                                    <input type="checkbox" name="materials[{{ $idx }}][id]" value="{{ $rm->id }}" class="rounded text-brand-600">
                                    <span>{{ $rm->name }}</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <span class="text-slate-500 text-[11px]">التكلفة: <b class="text-slate-800">{{ number_format($rm->unit_cost, 2) }} ج.م</b></span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-slate-400">الكمية:</span>
                                        <input type="number" name="materials[{{ $idx }}][qty]" value="1" min="0.1" step="0.1" class="w-14 px-2 py-0.5 border border-slate-200 rounded text-center text-xs font-bold">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('customProductModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow">
                        حفظ واحتساب التكلفة التلقائية
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders History for this customer -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-clock-rotate-left text-brand-600"></i>
            <span>سجل طلبيات العميل السابقة والجارية</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم الطلب</th>
                        <th class="py-3 px-4">تاريخ الطلب</th>
                        <th class="py-3 px-4">المنتج والكمية</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">حالة الدورة</th>
                        <th class="py-3 px-4">موعد التسليم</th>
                        <th class="py-3 px-4 text-center">تفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->salesOrders as $so)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">{{ $so->order_number }}</td>
                            <td class="py-3 px-4">{{ $so->order_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">{{ $so->items->first()?->product->name ?? 'طلب متعدد' }}</td>
                            <td class="py-3 px-4 font-black">{{ number_format($so->total_amount, 2) }} ج.م</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $so->status_badge['color'] }}">
                                    {{ $so->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium">{{ $so->confirmed_delivery_date ? $so->confirmed_delivery_date->format('Y-m-d') : 'قيد المراجعة' }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('sales.orders.show', $so->id) }}" class="px-3 py-1 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg font-bold text-xs">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-400">لا توجد طلبات سابقة لهذا العميل.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
