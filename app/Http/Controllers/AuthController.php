<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->forget('url.intended');
            $request->session()->forget('error');
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'مرحباً بك يا '.Auth::user()->name.' في نظام ألفا إيجبت ERP');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة، يرجى التأكد من البريد الإلكتروني وكلمة المرور.',
        ])->onlyInput('email');
    }

    public function fastLogin(Request $request, $id = null): RedirectResponse
    {
        abort(403, 'تم إيقاف الدخول السريع وتبديل الأدوار في بيئة التشغيل الفعلية. يرجى تسجيل الدخول بالبريد الإلكتروني وكلمة المرور.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'تم تسجيل الخروج بنجاح.');
    }

    public function profile(): View
    {
        $user = Auth::user();

        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'user_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);
            $user->avatar = 'uploads/users/'.$filename;
        }

        $user->save();

        return back()->with('success', 'تم تحديث بيانات الملف الشخصي والصورة بنجاح.');
    }
}
