<?php

namespace App\Http\Requests\Admin\Users\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Validation\Rule; // <--- OBLIGATORIO IMPORTAR ESTO

class UpsertCustomerRequest extends FormRequest
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
        // Capturamos el UUID de la ruta. En Route::resource('users', ...) el parámetro se llama 'user'
        $customerId = $this->route('user'); 
        $isUpdate = $customerId !== null;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => $isUpdate ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'],
            
            // ====================================================================
            // REGLA ZERO-TRUST: EXCLUSIÓN DE ID PROPIO Y VALIDACIÓN MULTI-SILO
            // ====================================================================
            'email' => [
                'required', 
                'email', 
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId), // Ignora a sí mismo
                Rule::unique('admins', 'email'),                         // Valida en Admin
                Rule::unique('drivers', 'email'),                        // Valida en Drivers
            ],
            'phone' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('customers', 'phone')->ignore($customerId), // Ignora a sí mismo
                Rule::unique('admins', 'phone'),                         // Valida en Admin
                Rule::unique('drivers', 'phone'),                        // Valida en Drivers
            ],
            // ====================================================================
            
            'branch_id'  => ['nullable', 'string', 'exists:branches,id'],
            'is_active'  => ['nullable', 'boolean'],
            
            // Ubicación: Obligatoria al crear, Opcional al editar
            'latitude'  => $isUpdate ? ['nullable', 'numeric'] : ['required', 'numeric'],
                'longitude' => $isUpdate ? ['nullable', 'numeric'] : ['required', 'numeric'],
                'address'   => $isUpdate ? ['nullable', 'string']  : ['required', 'string'],
                'details'   => ['nullable', 'string', 'max:500'], // <--- AÑADIR ESTA LÍNEA
            ];
    }
}