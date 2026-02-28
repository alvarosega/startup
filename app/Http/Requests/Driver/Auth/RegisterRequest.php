<?php

namespace App\Http\Requests\Driver\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool 
    { 
        return true; 
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            // Identidad Global
            'phone'          => $this->globalPhoneRules(),
            'email'          => $this->globalEmailRules(),
            'password'       => ['required', 'string', 'min:8', 'confirmed'],

            // Datos del DriverDetail
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string'],
            'license_plate'  => ['required', 'string'],
            'vehicle_type'   => ['required', 'string', 'in:moto,car,truck'],
        ];
    }
}