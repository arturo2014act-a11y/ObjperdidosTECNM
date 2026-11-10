<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Formulario de solicitud de recuperación de contraseña (RF-28).
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Enviar enlace de restablecimiento con respuesta genérica anti-enumeración (RF-77).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Debes ingresar tu correo institucional.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        // RF-77: Si la cuenta existe y está activa, enviamos el enlace
        if ($user && $user->is_active) {
            Password::sendResetLink(['email' => $email]);
        }

        // RF-77: Mismo mensaje genérico para cuentas existentes o inexistentes
        return back()->with('status', 'Revisa tu bandeja de entrada. Hemos enviado un enlace de recuperación al correo proporcionado, en caso de que este pertenezca a una cuenta activa en la plataforma.');
    }

    /**
     * Formulario para ingresar la nueva contraseña desde el enlace temporal (RF-28, RF-71).
     */
    public function edit(Request $request, string $token): View|RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email')));
        $user = User::where('email', $email)->first();

        // RF-71: Validar si el usuario existe y si el token sigue vigente en password_reset_tokens
        if (!$user || !Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('password.request')
                ->with('error', 'El enlace de recuperación es inválido, ya fue utilizado o ha expirado. Por favor, solicita uno nuevo.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed', // RF-74
                PasswordRule::min(8) // RF-73
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'token.required' => 'El token de recuperación es obligatorio.',
            'email.required' => 'El correo institucional es obligatorio.',
            'password.required' => 'Debes ingresar tu nueva contraseña.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        // Restablecimiento mediante broker de Laravel
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password), // RF-75
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // RF-76: Redireccionar a login con mensaje sin auto-login
            return redirect()->route('login')->with('success', 'Contraseña restablecida con éxito. Inicia sesión para continuar.');
        }

        // RF-71: Enlace inválido o expirado
        throw ValidationException::withMessages([
            'email' => ['El enlace de recuperación ha expirado. Tu contraseña actual sigue vigente. Solicita uno nuevo.'],
        ]);
    }
}
