<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * RF-67: Las cuentas desactivadas no podrán iniciar sesión ni navegar.
     * Si la cuenta fue desactivada mientras tenía una sesión abierta,
     * se expulsa inmediatamente invalidando su sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada por el administrador. Si consideras que se trata de un error, contacta a soporte.');
        }

        return $next($request);
    }
}

