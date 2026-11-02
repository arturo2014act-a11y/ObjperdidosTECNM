<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Muestra aviso de correo sin verificar (RF-68).
     */
    public function prompt(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('items.index'))
            : view('auth.verify-email');
    }

    /**
     * Verifica el correo tras hacer clic en el enlace (RF-10, RF-68, RF-69).
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return redirect()->route('login')->with('error', 'El enlace de verificación es inválido.');
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')->with('error', 'El enlace de verificación es inválido o ha sido modificado.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('items.index').'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('items.index').'?verified=1')->with('success', '¡Tu correo institucional ha sido verificado con éxito!');
    }

    /**
     * Reenviar notificación de verificación (RF-68).
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('items.index'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Enlace enviado. Revisa tu bandeja de entrada en el correo institucional.');
    }
}
