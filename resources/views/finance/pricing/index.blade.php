@extends('layouts.app')

@section('title', 'محرك التسعير وتكاليف المنتجات - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">محرك التسعير وهندسة التكاليف (Costing Engine)</h1>
            <p class="text-sm text-slate-500 mt-1">تحديد التكلفة المباشرة من واقع بطاقة المكونات (BOM) ومراقبة هوامش أرباح الكوڤير والمنتجات الأخرى</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.lossAlarms') }}" class="btn-danger flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>مراقبة إنذارات الخسارة</span>
            </a>
            <a href="{{ route('finance.index') }}" class="btn-secondary flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                <span>الماليات</span>
            </a>
        </div>
    </div>

    <!-- Alert Explanation Box -->
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-r-4 border-amber-500 p-5 rounded-2xl bg-white shadow-sm border border-slate-200/80">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calculator text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">سياسة التسعير وهوامش الأرباح بشركة الفا ايجبت</h3>
                <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                    يتم احتساب تكلفة المنتج بناءً على معادلة دقيقة تجمع أسعار الخامات من بطاقة المكونات (<span class="font-mono font-bold">BOM</span>) مضافاً إليها تكاليف التصنيع الداخلي للمواد الوسيطة. 
                    <strong class="text-rose-600 font-bold">تنبيه حرج:</strong> بالنسبة لمنتجات <span class="font-bold text-primary-700">الكوڤير المخصص</span> المتعاقد عليه بسعر ثابت؛ إذا تجاوزت التكلفة سعر البيع يطلق النظام جرس إنذار فوري وتنبيه للإدارة العليا لمراجعة العقد دون إيقاف التوريد حرصاً على سمعة الشركة والتزاماتها.
                </p>
            </div>
        </div>
    </div>

    <!-- Pricing Cards / Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-tags text-primary-600"></i>
                <span>قائمة المنتجات وهيكل الأسعار والتكاليف</span>
            </h2>
            <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-600 rounded-full">إجمالي المنتجات: {{ $products->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600 font-bold">
                        <th class="py-4 px-6">كود واسم المنتج</th>
                        <th class="py-4 px-6">المصنع / العميل</th>
                        <th class="py-4 px-6">مكونات الـ BOM</th>
                        <th class="py-4 px-6">التكلفة المحسوبة</th>
                        <th class="py-4 px-6">سعر البيع الرسمي</th>
                        <th class="py-4 px-6">أدنى سعر بيع</th>
                        <th class="py-4 px-6">صافي الربح / الوحدة</th>
                        <th class="py-4 px-6 text-center">الحالة</th>
                        <th class="py-4 px-6 text-center">إجراءات وتحديث</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $prod)
                    @php
                        $unitProfit = $prod->selling_price - $prod->cost_price;
                        $marginPct = $prod->cost_price > 0 ? ($unitProfit / $prod->cost_price) * 100 : 0;
                        $isLoss = $prod->selling_price < $prod->cost_price;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $isLoss ? 'bg-rose-50/40' : '' }}">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $isLoss ? 'bg-rose-100 text-rose-600' : 'bg-primary-50 text-primary-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 block text-base">{{ $prod->name }}</span>
                                    <span class="text-xs font-mono text-slate-400">{{ $prod->code }} | {{ $prod->unit }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            @if($prod->customer)
                                <div class="flex items-center gap-2">
                                    @if($prod->customer->logo)
                                        <img src="{{ asset($prod->customer->logo) }}" class="w-6 h-6 rounded-full object-contain border border-slate-200 bg-white" alt="">
                                    @endif
                                    <span class="font-semibold text-slate-700 text-xs">{{ $prod->customer->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-500">منتج عام قياسي</span>
                            @endif
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $prod->factory?->name ?? 'خط التصنيع المركزي' }}
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <button type="button" onclick="document.getElementById('bomModal-{{ $prod->id }}').classList.remove('hidden')" class="text-xs font-semibold text-primary-600 hover:text-primary-800 underline flex items-center gap-1">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>{{ $prod->boms->count() }} مكونات خام</span>
                            </button>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-700 text-base">
                            {{ number_format($prod->cost_price, 2) }} <span class="text-xs text-slate-400 font-sans">ج.م</span>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-800 text-base">
                            {{ number_format($prod->selling_price, 2) }} <span class="text-xs text-slate-400 font-sans">ج.م</span>
                        </td>
                        <td class="py-4 px-6 font-mono text-slate-500 text-xs">
                            {{ number_format($prod->min_selling_price, 2) }} ج.م
                        </td>
                        <td class="py-4 px-6">
                            @if($isLoss)
                                <div class="text-rose-600 font-bold font-mono">
                                    <span>{{ number_format($unitProfit, 2) }} ج.م</span>
                                    <span class="block text-[11px]">({{ number_format($marginPct, 1) }}%)</span>
                                </div>
                            @else
                                <div class="text-emerald-600 font-bold font-mono">
                                    <span>+{{ number_format($unitProfit, 2) }} ج.م</span>
                                    <span class="block text-[11px] text-emerald-500">(+{{ number_format($marginPct, 1) }}%)</span>
                                </div>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($isLoss)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-300 animate-pulse">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>يباع بخسارة</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>ربحي متوافق</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Recalculate BOM -->
                                <form action="{{ route('finance.pricing.recalculate', $prod->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="إعادة احتساب التكلفة من الـ BOM" class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors border border-slate-200">
                                        <i class="fa-solid fa-arrows-rotate"></i>
                                    </button>
                                </form>

                                <!-- Edit Price Modal Button -->
                                <button type="button" onclick="document.getElementById('editModal-{{ $prod->id }}').classList.remove('hidden')" title="تعديل الأسعار والهوامش" class="p-2 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-slate-200">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- BOM Details Modal -->
                    <div id="bomModal-{{ $prod->id }}" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
                        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg">مكونات وتكاليف تصنيع: {{ $prod->name }}</h3>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $prod->code }} | الكوڤير ومكوناته</p>
                                </div>
                                <button type="button" onclick="document.getElementById('bomModal-{{ $prod->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-right text-xs">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                                                <th class="p-3">المادة الخام</th>
                                                <th class="p-3">الكمية</th>
                                                <th class="p-3">سعر الوحدة</th>
                                                <th class="p-3">إجمالي التكلفة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-mono">
                                            @php $calcTotal = 0; @endphp
                                            @foreach($prod->boms as $b)
                                            @php 
                                                $compCost = $b->quantity * $b->unit_cost; 
                                                $calcTotal += $compCost;
                                            @endphp
                                            <tr>
                                                <td class="p-3 font-sans font-semibold text-slate-800">
                                                    {{ $b->rawMaterial?->name ?? 'مكون غير محدد' }}
                                                </td>
                                                <td class="p-3 text-slate-600">{{ $b->quantity }} {{ $b->rawMaterial?->unit ?? '' }}</td>
                                                <td class="p-3 text-slate-600">{{ number_format($b->unit_cost, 4) }} ج.م</td>
                                                <td class="p-3 font-bold text-slate-800">{{ number_format($compCost, 4) }} ج.م</td>
                                            </tr>
                                            @endforeach
                                            <tr class="bg-primary-50/50 font-bold text-sm">
                                                <td colspan="3" class="p-3 text-slate-800 font-sans">إجمالي التكلفة المباشرة للوحدة من الـ BOM:</td>
                                                <td class="p-3 text-primary-700">{{ number_format($calcTotal, 4) }} ج.م</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                                <button type="button" onclick="document.getElementById('bomModal-{{ $prod->id }}').classList.add('hidden')" class="btn-secondary text-xs">إغلاق</button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Price Modal -->
                    <div id="editModal-{{ $prod->id }}" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
                        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
                            <form action="{{ route('finance.pricing.update', $prod->id) }}" method="POST">
                                @csrf
                                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-lg">تحديث تسعير: {{ $prod->name }}</h3>
                                    <button type="button" onclick="document.getElementById('editModal-{{ $prod->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-2">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">التكلفة المباشرة (ج.م)</label>
                                        <input type="number" step="0.0001" min="0" name="cost_price" value="{{ $prod->cost_price }}" required class="input-field font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">سعر البيع المعتمد (ج.م)</label>
                                        <input type="number" step="0.01" min="0" name="selling_price" value="{{ $prod->selling_price }}" required class="input-field font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">أدنى سعر بيع مسموح به لقسم المبيعات (ج.م)</label>
                                        <input type="number" step="0.01" min="0" name="min_selling_price" value="{{ $prod->min_selling_price }}" required class="input-field font-mono">
                                        <p class="text-[11px] text-slate-400 mt-1">يمنع مسؤول المبيعات من النزول عن هذا الحد</p>
                                    </div>
                                </div>
                                <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('editModal-{{ $prod->id }}').classList.add('hidden')" class="btn-secondary text-xs">إلغاء</button>
                                    <button type="submit" class="btn-primary text-xs">حفظ التسعير الجديد</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">لا توجد منتجات مسجلة في النظام بعد.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
