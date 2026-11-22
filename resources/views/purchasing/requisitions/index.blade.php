@extends('layouts.app')

@section('title', 'طلبات النواقص من المخزن')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-bell-concierge text-purple-600"></i>
                <span>طلبات النواقص الواردة من المخزن المركزي</span>
            </h1>
            <p class="text-xs text-slate-500">مطلوب الرد بالمدة الزمنية التي ستوفر بها المشتريات الخامة لتأكيد موعد تسليم العميل</p>
        </div>
        <a href="{{ route('purchasing.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
            ← رجوع للمشتريات
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-3 px-4">رقم الطلب</th>
                        <th class="py-3 px-4">أمر التشغيل / العميل</th>
                        <th class="py-3 px-4">تاريخ طلب النواقص</th>
                        <th class="py-3 px-4">الأهمية</th>
                        <th class="py-3 px-4">مدة التوريد المحددة</th>
                        <th class="py-3 px-4">الموعد المتوقع للوصول</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requisitions as $pr)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $pr->pr_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $pr->salesOrder->customer->name ?? 'طلب مخزن عام' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $pr->created_at->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pr->urgency === 'critical' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $pr->urgency === 'critical' ? 'حرجة جداً' : 'عادية' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-black text-purple-700">
                                {{ $pr->procurement_lead_time_days > 0 ? $pr->procurement_lead_time_days . ' أيام' : 'بانتظار الرد' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-600">
                                {{ $pr->estimated_arrival_date ? $pr->estimated_arrival_date->format('Y-m-d') : 'غير محدد' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $pr->status === 'lead_time_quoted' || $pr->status === 'po_issued' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $pr->status === 'lead_time_quoted' ? 'تم الرد بالمدة' : ($pr->status === 'po_issued' ? 'أمر شراء صادر' : 'بانتظار الرد') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('purchasing.requisitions.show', $pr->id) }}" class="px-3 py-1 bg-purple-50 hover:bg-purple-100 text-purple-800 font-bold rounded-lg transition">
                                    تحديد المدة وأمر الشراء
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">لا توجد طلبات نواقص مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100">
            {{ $requisitions->links() }}
        </div>
    </div>

</div>
@endsection
