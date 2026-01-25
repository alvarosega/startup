<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Datos de Cuenta (users)
            'email' => [
                'required', 'string', 'email', 'max:255', 
                Rule::unique('users')->ignore($this->user()->id)
            ],

            // Datos Personales (user_profiles)
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:M,F,O'], // M=Masculino, F=Femenino, O=Otro
        ];
    }
}