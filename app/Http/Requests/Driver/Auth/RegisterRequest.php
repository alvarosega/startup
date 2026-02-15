<?php

namespace App\Http\Requests\Driver\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Traits\ValidatesGlobalIdentity; // <--- AÑADIR

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; } // <--- CAMBIAR A TRUE

    protected function prepareForValidation() 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            // Identidad Global
            'phone' => $this->globalPhoneRules(),
            'email' => $this->globalEmailRules(),
            
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            
            // Datos del Vehículo (Basado en tu migración)
            'license_number' => ['required', 'string', 'unique:driver_details,license_number'],
            'license_plate'  => ['required', 'string', 'max:10'],
            'vehicle_type'   => ['required', 'string'],
        ];
    }
}