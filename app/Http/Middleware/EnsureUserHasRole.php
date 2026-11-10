<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * RF-62: Acceso no autorizado entre roles.
     * Si una cuenta autenticada intenta abrir una ruta de otro rol,
     * se responderá con HTTP 404 Not Found, sin revelar la existencia del recurso.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(404);
        }

        // Permitir acceso si el usuario tiene el rol asignado o si es rol admin (supervisión global)
        if (in_array($user->role, $roles, true) || $user->role === 'admin') {
            return $next($request);
        }

        // RF-62: Bloqueo con 404 Not Found para no revelar rutas internas
        abort(404);
    }
}