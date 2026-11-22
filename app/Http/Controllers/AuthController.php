<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $users = User::where('status', true)->get();
        return view('auth.login', compact('users'));
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
            return redirect()->route('dashboard')->with('success', 'مرحباً بك يا ' . Auth::user()->name . ' في نظام ألفا إيجبت ERP');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة، يرجى التأكد من البريد الإلكتروني وكلمة المرور.',
        ])->onlyInput('email');
    }

    public function fastLogin(Request $request, $id = null): RedirectResponse
    {
        $userId = $id ?? $request->input('user_id') ?? $request->query('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'يرجى اختيار المستخدم لتسجيل الدخول.');
        }

        $user = User::findOrFail($userId);
        Auth::login($user);
        
        // Clear any intended URL and prior error flash from previous role
        $request->session()->forget('url.intended');
        $request->session()->forget('error');
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'تم الدخول بنجاح بحساب: ' . $user->name . ' (' . $user->role_arabic_name . ')');
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

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);
            $user->avatar = 'uploads/users/' . $filename;
        }

        $user->save();

        return back()->with('success', 'تم تحديث بيانات الملف الشخصي والصورة بنجاح.');
    }
}
