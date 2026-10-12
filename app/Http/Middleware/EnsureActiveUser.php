<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        // Revocamos también las sesiones que estaban abiertas antes de desactivar la cuenta.
        if ($request->user() && (!$request->user()->is_active || $request->user()->isDoctor())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('status', 'Esta cuenta fue desactivada.');
        }
        return $next($request);
    }
}
