@extends('layouts.app')

@section('title', 'إدارة المستخدمين والصلاحيات - الفا ايجبت')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">إدارة المستخدمين وصلاحيات المديرين</h1>
            <p class="text-sm text-slate-500 mt-1">التحكم في حسابات الإدارة العليا ومديري الأقسام والصلاحيات وكلمات المرور</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index') }}" class="px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-purple-200">
                <i class="fa-solid fa-list-check"></i>
                <span>المهام والتكليفات الإضافية</span>
            </a>
            <a href="{{ route('users.create') }}" class="btn-primary flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة موظف / مهندس جديد</span>
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-primary-600"></i>
                <span>فريق الإدارة والتشغيل بنظام الفا ايجبت ({{ $users->count() }})</span>
            </h2>
            <span class="text-xs font-semibold px-3 py-1 bg-primary-50 text-primary-700 rounded-full">
                صلاحيات مخصصة لكل قسم
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600 font-bold">
                        <th class="py-4 px-6">المسؤول / المدير</th>
                        <th class="py-4 px-6">المسمى والوظيفة</th>
                        <th class="py-4 px-6">القسم / الإدارة</th>
                        <th class="py-4 px-6">مستوى الصلاحية (Role)</th>
                        <th class="py-4 px-6">رقم الهاتف</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $userItem)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                @if($userItem->avatar)
                                    <img src="{{ asset($userItem->avatar) }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-sm" alt="{{ $userItem->name }}">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-base shadow-sm">
                                        {{ mb_substr($userItem->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-bold text-slate-800 block text-base">{{ $userItem->name }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ $userItem->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-700">
                            {{ $userItem->job_title }}
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs">
                            {{ $userItem->department }}
                        </td>
                        <td class="py-4 px-6">
                            @switch($userItem->role)
                                @case('owner')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-crown text-amber-600"></i>
                                        <span>رئيس مجلس الإدارة (مالك)</span>
                                    </span>
                                    @break
                                @case('gm')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 border border-primary-300">
                                        <i class="fa-solid fa-shield-halved text-primary-700"></i>
                                        <span>المدير العام (GM)</span>
                                    </span>
                                    @break
                                @case('sales')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-handshake"></i>
                                        <span>مدير المبيعات و CRM</span>
                                    </span>
                                    @break
                                @case('operations')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                        <i class="fa-solid fa-gears"></i>
                                        <span>مدير التشغيل والإنتاج</span>
                                    </span>
                                    @break
                                @case('warehouse')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        <span>مدير المخازن والجرد</span>
                                    </span>
                                    @break
                                @case('purchasing')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <span>مدير المشتريات والتوريد</span>
                                    </span>
                                    @break
                                @case('finance')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-800">
                                        <i class="fa-solid fa-sack-dollar"></i>
                                        <span>مدير الحسابات والماليات</span>
                                    </span>
                                    @break
                                @case('maintenance')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-wrench"></i>
                                        <span>مدير الصيانة الفنية</span>
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="py-4 px-6 font-mono text-xs text-slate-600">
                            {{ $userItem->phone ?? 'غير مسجل' }}
                        </td>
                        <td class="py-4 px-6">
                            @if($userItem->status)
                                <span class="badge badge-success text-[11px]">نشط</span>
                            @else
                                <span class="badge badge-danger text-[11px]">معطل</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('tasks.index', ['assigned_to' => $userItem->id]) }}" title="عرض وتكليف مهام للمستخدم" class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors border border-purple-200">
                                    <i class="fa-solid fa-list-check"></i>
                                </a>
                                <a href="{{ route('users.edit', $userItem->id) }}" title="تعديل الحساب والصلاحيات" class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors border border-slate-200">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                <button type="button" onclick="openResetPasswordModal('{{ $userItem->id }}', '{{ $userItem->name }}')" title="إعادة تعيين كلمة المرور" class="p-2 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-slate-200">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPwdModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
            <form id="resetPwdForm" action="" method="POST">
                @csrf
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">إعادة تعيين كلمة المرور</h3>
                        <p class="text-xs text-slate-500 mt-0.5" id="resetModalUserName"></p>
                    </div>
                    <button type="button" onclick="closeResetPasswordModal()" class="text-slate-400 hover:text-slate-600 p-2">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">كلمة المرور الجديدة <span class="text-rose-500">*</span></label>
                        <input type="password" name="new_password" required minlength="6" placeholder="******" class="input-field">
                        <p class="text-[11px] text-slate-400 mt-1">يجب ألا تقل عن 6 أحرف</p>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeResetPasswordModal()" class="btn-secondary text-xs">إلغاء</button>
                    <button type="submit" class="btn-primary text-xs">تحديث كلمة المرور</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetPasswordModal(userId, userName) {
    document.getElementById('resetPwdForm').action = '/users/' + userId + '/reset-password';
    document.getElementById('resetModalUserName').textContent = 'للمستخدم: ' + userName;
    document.getElementById('resetPwdModal').classList.remove('hidden');
}
function closeResetPasswordModal() {
    document.getElementById('resetPwdModal').classList.add('hidden');
}
</script>
@endsection
