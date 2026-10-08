<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403, 'No tiene permiso para acceder a esta seccion.');
        }

        return $next($request);
    }
}