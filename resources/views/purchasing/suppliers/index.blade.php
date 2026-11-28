@extends('layouts.app')

@section('title', 'سجل الموردين والتصنيع الخارجي')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-handshake text-purple-600"></i>
                <span>سجل الموردين وشركاء التصنيع عند الغير</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">موردي خرز البلاستيك، الورق، وشركاء التصنيع الخارجي (حقن المعالق والشوك، رولات التغليف السيلوفان، تغليف الجوانتيات، ومستلزمات الضيافة)</p>
        </div>
        <button type="button" onclick="openNewSupplierModal()" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إضافة مورد / شريك جديد</span>
        </button>
    </div>

    <!-- Alert / Summary -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-100 rounded-2xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-purple-900">إجمالي الموردين والشركاء المعتمدين</h4>
                <p class="text-[11px] text-purple-700">مسجل حالياً {{ $suppliers->count() }} مورد ومصنع شريك، شامل أسعار المصنعيات ومواعيد التوريد المتفق عليها.</p>
            </div>
        </div>
        <span class="px-3 py-1 bg-white text-purple-700 font-black text-sm rounded-xl border border-purple-200 shadow-sm">
            {{ $suppliers->count() }} جهة
        </span>
    </div>

    <!-- Suppliers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($suppliers as $s)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ str_contains($s->category, 'خارجي') ? 'bg-purple-100 text-purple-800' : (str_contains($s->category, 'تعبئة') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $s->category }}
                            </span>
                            <h3 class="font-extrabold text-base text-slate-900 mt-1.5">{{ $s->name }}</h3>
                            <span class="font-mono text-xs font-bold text-slate-400">{{ $s->code }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs font-bold bg-amber-50 px-2 py-1 rounded-lg">
                            <i class="fa-solid fa-star"></i>
                            <span>{{ $s->rating }}</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                        @if($s->contact_person)
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-user-tie text-slate-400 text-[10px]"></i>
                                <span>المسؤول: <b>{{ $s->contact_person }}</b></span>
                            </div>
                        @endif
                        @if($s->phone)
                            <div class="flex items-center gap-1.5" dir="ltr">
                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                <span class="font-mono font-semibold">{{ $s->phone }}</span>
                            </div>
                        @endif
                        @if($s->email)
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <i class="fa-solid fa-envelope text-slate-400 text-[10px]"></i>
                                <span class="truncate">{{ $s->email }}</span>
                            </div>
                        @endif
                        @if($s->address)
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <i class="fa-solid fa-location-dot text-slate-400 text-[10px]"></i>
                                <span class="truncate">{{ $s->address }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5 text-purple-700 font-bold">
                            <i class="fa-solid fa-clock text-purple-400 text-[10px]"></i>
                            <span>متوسط مدة التوريد: {{ $s->average_lead_time_days }} أيام</span>
                        </div>
                    </div>

                    <!-- Notes & Pricing Agreements -->
                    @if($s->notes)
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 text-[11px] text-slate-700 space-y-1">
                            <div class="font-bold text-purple-900 flex items-center gap-1">
                                <i class="fa-solid fa-file-contract text-purple-600 text-[10px]"></i>
                                <span>المنتجات وأسعار المصنعية والتوريد:</span>
                            </div>
                            <p class="whitespace-pre-line text-slate-600 leading-relaxed">{{ $s->notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <button type="button" 
                            onclick='openEditSupplierModal(@json($s))'
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-pen-to-square text-slate-500"></i>
                        <span>تعديل المورد</span>
                    </button>
                    <a href="{{ route('purchasing.orders.create') }}" class="font-bold text-purple-600 hover:text-purple-800 transition flex items-center gap-1">
                        <span>إصدار أمر توريد</span>
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal to Add New Supplier -->
    <div id="newSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-purple-600"></i>
                    <span>إضافة مورد / شريك تصنيع جديد</span>
                </h3>
                <button type="button" onclick="closeNewSupplierModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
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
                            <option value="تعبئة وتغليف">مواد تعبئة وتغليف</option>
                            <option value="قطع غيار">قطع غيار ماكينات</option>
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
                        <input type="text" name="phone" dir="ltr" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none text-right">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">متوسط مدة التوريد (أيام) *</label>
                        <input type="number" name="average_lead_time_days" value="3" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">العنوان أو المقر</label>
                    <input type="text" name="address" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بيانات المنتجات، أسعار المصنعية، وملاحظات الاتفاق</label>
                    <textarea name="notes" rows="3" placeholder="اكتب الأصناف الموردة، أسعار المصنعيات، وشروط الاتفاق..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeNewSupplierModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">حفظ المورد</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal to Edit Existing Supplier -->
    <div id="editSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-purple-600"></i>
                    <span>تعديل بيانات المورد / المصنع الشريك</span>
                </h3>
                <button type="button" onclick="closeEditSupplierModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editSupplierForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم المورد / المصنع *</label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">كود المورد</label>
                        <input type="text" id="edit_code" disabled class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف *</label>
                        <select id="edit_category" name="category" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-purple-600 outline-none">
                            <option value="خامات">توريد خامات أولية</option>
                            <option value="تصنيع خارجي عند الغير">تصنيع خارجي عند الغير</option>
                            <option value="تعبئة وتغليف">مواد تعبئة وتغليف</option>
                            <option value="قطع غيار">قطع غيار ماكينات</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">مسؤول التواصل</label>
                        <input type="text" id="edit_contact_person" name="contact_person" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهاتف</label>
                        <input type="text" id="edit_phone" name="phone" dir="ltr" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none text-right">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" id="edit_email" name="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">متوسط مدة التوريد (أيام) *</label>
                        <input type="number" id="edit_average_lead_time_days" name="average_lead_time_days" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">العنوان أو المقر</label>
                    <input type="text" id="edit_address" name="address" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بيانات المنتجات، أسعار المصنعية، وملاحظات الاتفاق</label>
                    <textarea id="edit_notes" name="notes" rows="3" placeholder="اكتب الأصناف الموردة، أسعار المصنعيات، وشروط الاتفاق..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-1 focus:ring-purple-600 outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeEditSupplierModal()" class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openNewSupplierModal() {
    const modal = document.getElementById('newSupplierModal');
    if (modal) modal.classList.remove('hidden');
}

function closeNewSupplierModal() {
    const modal = document.getElementById('newSupplierModal');
    if (modal) modal.classList.add('hidden');
}

function openEditSupplierModal(supplier) {
    const modal = document.getElementById('editSupplierModal');
    const form = document.getElementById('editSupplierForm');
    if (!modal || !form || !supplier) return;

    form.action = '/purchasing/suppliers/' + supplier.id;
    document.getElementById('edit_name').value = supplier.name || '';
    document.getElementById('edit_code').value = supplier.code || '';
    document.getElementById('edit_category').value = supplier.category || 'خامات';
    document.getElementById('edit_contact_person').value = supplier.contact_person || '';
    document.getElementById('edit_phone').value = supplier.phone || '';
    document.getElementById('edit_email').value = supplier.email || '';
    document.getElementById('edit_address').value = supplier.address || '';
    document.getElementById('edit_average_lead_time_days').value = supplier.average_lead_time_days || 3;
    document.getElementById('edit_notes').value = supplier.notes || '';

    modal.classList.remove('hidden');
}

function closeEditSupplierModal() {
    const modal = document.getElementById('editSupplierModal');
    if (modal) modal.classList.add('hidden');
}

// Global click outside to close modals
window.addEventListener('click', function(e) {
    const newModal = document.getElementById('newSupplierModal');
    const editModal = document.getElementById('editSupplierModal');
    if (e.target === newModal) closeNewSupplierModal();
    if (e.target === editModal) closeEditSupplierModal();
});

// Escape key to close modals
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewSupplierModal();
        closeEditSupplierModal();
    }
});
</script>
@endsection
