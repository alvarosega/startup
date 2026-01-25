<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        if ($this->has('phone')) {
            $phone = str_replace([' ', '-', '(', ')'], '', $this->phone);
            if (!str_starts_with($phone, '+')) {
                $phone = '+591' . $phone;
            }
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            // Cuenta
            'phone' => ['required', 'string', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // <--- Obligatorio
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],

            // Datos Personales (Driver Profile)
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:20', 'unique:driver_profiles,license_number'],
            'license_plate' => ['required', 'string', 'max:10'],
            'vehicle_type' => ['required', 'in:moto,car,truck'],

            // Avatar
            'avatar_type' => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'image', 'max:2048'],
            
            // Rol (debería venir fijo o ignorarse)
            'role' => ['nullable'] 
        ];
    }
}