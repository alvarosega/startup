<?php

namespace App\Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /**
     * Limpieza previa a la validación.
     * Esto asegura que la regla 'unique' compare peras con peras (+591 vs +591).
     */
    protected function prepareForValidation()
    {
        if ($this->has('phone')) {
            $phone = str_replace([' ', '-', '(', ')'], '', $this->phone);
            
            // Si falta el +, asumimos Bolivia y lo agregamos
            if (!str_starts_with($phone, '+')) {
                $phone = '+591' . $phone;
            }

            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            // Regex: Debe empezar con + y tener entre 8 y 15 dígitos
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{8,15}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:client,driver'],
            'terms' => ['accepted'],
            // Validaciones para el Avatar
            'avatar_type' => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'], // Para iconos predefinidos
            'avatar_file' => ['nullable', 'image', 'max:2048'], // Para subidas (Max 2MB)
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Este número ya está registrado. Intente iniciar sesión.',
            'phone.regex' => 'El formato del teléfono es inválido (Ej: +591...)',
            'terms.accepted' => 'Debe aceptar los términos para continuar.',
        ];
    }
}