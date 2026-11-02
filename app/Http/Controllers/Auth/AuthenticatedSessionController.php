<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión (RF-09).
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa la autenticación con limitación de intentos (RF-65) y validación de estado activo (RF-67).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Debes ingresar tu correo institucional.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // RF-65: Control de intentos fallidos (máximo 5 intentos por minuto)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Acceso bloqueado temporalmente por seguridad. Inténtalo más tarde.',
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // RF-67: Verificar si la cuenta se encuentra desactivada
        $user = User::where('email', strtolower(trim($request->input('email'))))->first();
        if ($user && !$user->is_active) {
            RateLimiter::clear($throttleKey);
            return back()->withInput($request->only('email'))->with('error', 'Tu cuenta ha sido desactivada. No es posible iniciar sesión en este momento porque la cuenta está inactiva. Si consideras que se trata de un error, por favor contacta al administrador del sistema.');
        }

        Auth::attempt($credentials, $remember);
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('items.index'));
    }

    /**
     * Cierre de sesión (RF-63).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Has cerrado tu sesión correctamente.');
    }
}
