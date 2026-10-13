<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $usuario = $request->user();
        if (!$usuario) return redirect()->route('login');

        // Solo existen los roles de Fase 2. Los accesos médicos antiguos no se utilizan.
        if ($usuario->isDoctor() || !in_array($usuario->rol, ['admin', 'recepcionista', 'paciente'], true)
            || !in_array($usuario->rol, $roles, true)) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
