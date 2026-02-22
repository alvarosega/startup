<?php

namespace App\Http\Requests\Customer\Profiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Solo clientes autenticados pueden editar su perfil.
     */
    public function authorize(): bool
    {
        return Auth::guard('customer')->check();
    }

    /**
     * Reglas de validación para los datos de identidad.
     */
    public function rules(): array
    {
        $userId = Auth::guard('customer')->id();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required', 
                'email', 
                'max:255', 
                // Regla de Oro: Único en la tabla customers ignorando al usuario actual
                Rule::unique('customers', 'email')->ignore($userId)
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender'     => ['nullable', 'string', 'in:M,F,O'],
        ];
    }

    /**
     * Mensajes personalizados para una UX de Lujo.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Tu nombre es necesario para la identidad del perfil.',
            'last_name.required'  => 'Tu apellido es obligatorio.',
            'email.unique'        => 'Este correo ya está registrado en nuestra red.',
            'birth_date.before'   => 'La fecha de nacimiento no puede ser futura.',
            'gender.in'           => 'El género seleccionado no es válido.',
        ];
    }
}