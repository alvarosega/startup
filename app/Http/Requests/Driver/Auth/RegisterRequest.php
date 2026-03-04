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
            
            // CORRECCIÓN CRÍTICA: Validar que la licencia sea única
            'license_number' => ['required', 'string', 'unique:driver_details,license_number'], 
            
            'license_plate'  => ['required', 'string', 'max:10'], // Coincide con tu BD
            'vehicle_type'   => ['required', 'string', 'in:moto,car,truck'],
        ];
    }
    
    // Opcional pero recomendado para UX Premium:
    public function messages(): array
    {
        return [
            'license_number.unique' => 'Este número de licencia ya pertenece a otro conductor.',
        ];
    }
}