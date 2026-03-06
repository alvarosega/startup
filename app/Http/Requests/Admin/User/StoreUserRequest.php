<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class StoreUserRequest extends FormRequest
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
        // Al ser exclusivo para Customers, las reglas son directas y estrictas.
        // Cero lógica condicional, máxima velocidad.
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'string', 'min:6'],
            
            // Validación Global (Únicos en los 3 silos)
            'email'      => $this->globalEmailRules(),
            'phone'      => $this->globalPhoneRules(),
            
            // Datos Operativos y de Ubicación del Cliente
            'latitude'   => ['required', 'numeric'],
            'longitude'  => ['required', 'numeric'],
            'address'    => ['required', 'string'],
            
            // UUID son 36 caracteres. Mejor usamos 'exists' para asegurar integridad referencial
            'branch_id'  => ['nullable', 'string', 'exists:branches,id'], 
        ];
    }
}