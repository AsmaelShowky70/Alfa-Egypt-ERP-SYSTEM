<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Top management (Owner & GM) has universal access to all sections
        if ($user->isOwner() || $user->isGM()) {
            return $next($request);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // If unauthorized for this specific department, redirect to their role dashboard
        return redirect()->route('dashboard')->with('error', 'عفواً، صلاحيات حسابك لا تتيح لك الوصول لهذا القسم. تم توجيهك إلى لوحة التحكم الخاصة بمهامك.');
    }
}
