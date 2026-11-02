<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Nombre: obligatorio, máximo 100 caracteres y sin números (solo letras y espacios, incluye acentos y ñ)
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],

            // Correo: obligatorio, formato válido, único y restringido únicamente a @merida.tecnm.mx
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
                'regex:/^[a-zA-Z0-9._%+-]+@merida\.tecnm\.mx$/i',
            ],

            // Contraseña: obligatoria, confirmada, min 8 caracteres, mayúscula, minúscula, número y símbolo
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    /**
     * Mensajes de error personalizados para retroalimentación al usuario.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre completo es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',
            'name.regex' => 'El nombre no debe contener números ni caracteres especiales no permitidos.',
            
            'email.required' => 'El correo institucional es obligatorio.',
            'email.email' => 'Debes ingresar una dirección de correo electrónico válida.',
            'email.unique' => 'El correo ya ha sido registrado, por favor intente con uno nuevo. ¿No recuerdas tu contraseña? Haz clic en recuperar contraseña.',
            'email.regex' => 'Correo institucional no válido. Por favor, utiliza tu correo institucional asignado (@merida.tecnm.mx).',
            
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}

