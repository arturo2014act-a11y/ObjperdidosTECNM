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

        // RF-65: Umbral y duración definidos en config('auth.throttle')
        $maxAttempts = (int) config('auth.throttle.max_attempts', 5);
        $decaySeconds = (int) config('auth.throttle.decay_seconds', 60);

        // Control de intentos fallidos con bloqueo temporal y mensaje informativo con cuenta regresiva
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de acceso fallidos. Tu acceso ha sido bloqueado temporalmente por seguridad. Por favor, intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
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

        // Redirección según rol tras autenticación
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index');
        } elseif ($user->role === 'prefectura') {
            return redirect()->route('prefectura.dashboard');
        }

        return redirect()->route('items.index');
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
