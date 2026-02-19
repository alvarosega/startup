<?php

namespace App\Http\Requests\Driver\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Traits\ValidatesGlobalIdentity; 
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

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
            'phone' => $this->globalPhoneRules(),
            'email' => $this->globalEmailRules(),
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            
            // POSIBLE PUNTO DE FALLO:
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string', 'max:50'], // Quitamos el unique temporalmente para probar
            'license_plate' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'string', 'in:moto,car,truck'],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        Log::warning('[DriverRegister] Fallo de validación en el Request', [
            'errores' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation'])
        ]);

        parent::failedValidation($validator);
    }
}