<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RoleSwitchController extends Controller
{
    /**
     * Permite cambiar de rol a cualquier usuario mediante su correo institucional.
     * Si el usuario no existe en la BD, lo crea automáticamente para facilitar las pruebas.
     */
    public function switchRole(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@merida\.tecnm\.mx$/i',
            ],
            'role' => [
                'required',
                'string',
                'in:usuario_general,prefectura,admin',
            ],
        ], [
            'email.required' => 'Debes ingresar tu correo institucional.',
            'email.regex' => 'El correo debe pertenecer estrictamente al dominio institucional único @merida.tecnm.mx.',
            'role.required' => 'Debes seleccionar un rol a asignar.',
            'role.in' => 'El rol seleccionado no es válido en el sistema.',
        ]);

        $validated = $validator->validate();

        $email = strtolower(trim($validated['email']));
        $role = $validated['role'];

        // Buscar o crear usuario con ese correo
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $email)[0])),
                'password' => Hash::make('TecNM#2026'),
                'is_active' => true,
                'email_verified_at' => now(),
                'deactivation_reason' => null,
            ]
        );

        // Actualizar al rol seleccionado
        $user->role = $role;
        $user->save();

        // Autenticar la sesión con el nuevo rol
        Auth::login($user);

        $roleLabels = [
            'usuario_general' => 'Usuario General (Comunidad / Alumno)',
            'prefectura' => 'Prefectura (Revisión y Custodia)',
            'admin' => 'Administrador General',
        ];

        $label = $roleLabels[$role] ?? $role;

        return back()->with('success', "Has cambiado al rol '{$label}' con la cuenta {$user->email}.");
    }
}
