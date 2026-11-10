<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Vista de perfil de usuario (RF-83, RF-84).
     */
    public function edit(): View
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Modificar exclusivamente el nombre completo (RF-81, RF-82, RF-83).
     * El correo institucional es ineditable.
     */
    public function updateName(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100', // RF-81
                'regex:/^[\pL\s\-\'\.]+$/u', // RF-82: acepta letras, espacios, acentos, ñ, guiones, apóstrofos; rechaza números
            ],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'name.max' => 'El nombre no debe superar los 100 caracteres (RF-81).',
            'name.regex' => 'El nombre no debe contener números ni caracteres no permitidos (RF-82).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'nameUpdate')->withInput();
        }

        $validated = $validator->validated();
        $user->update(['name' => $validated['name']]);

        return back()->with('success', 'Tu nombre completo ha sido actualizado correctamente.');
    }

    /**
     * Cambio de contraseña en perfil (RF-73, RF-74, RF-84, RF-85).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'different:current_password', // No aceptar la misma contraseña en uso
                'confirmed', // RF-74
                Password::min(8) // RF-73
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual ingresada es incorrecta.',
            'password.required' => 'Debes ingresar una nueva contraseña.',
            'password.different' => 'La nueva contraseña no puede ser igual a tu contraseña actual.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'passwordUpdate')->withInput();
        }

        $validated = $validator->validated();

        $user->update([
            'password' => Hash::make($validated['password']), // RF-75
        ]);

        // RF-85: Sigue activa la sesión actual con confirmación
        return back()->with('success', 'Tu contraseña ha sido cambiada exitosamente.');
    }
}

