<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity; // <--- OBLIGATORIO

class UpdateUserRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // Eliminamos la lógica manual de +591 y usamos el estándar del sistema
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            // Mantenemos 'type' solo si permites editar otros silos desde aquí. 
            // Si el módulo es 100% Customer, 'type' debería ser opcional o fijo.
            'type'       => ['required', 'in:admin,customer,driver'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            
            // Inyectamos el ID para que la regla Unique ignore al usuario actual
            'phone'      => $this->globalPhoneRules($this->route('user')),
            'email'      => $this->globalEmailRules($this->route('user')),
            
            'password'   => ['nullable', 'string', 'min:6'],
            'role_id'    => ['required', 'exists:roles,id'],
            'branch_id'  => ['nullable', 'string', 'exists:branches,id'],
            'is_active'  => ['boolean'],
            
            // Campos de apoyo (se validan pero solo se usan si el type es driver)
            'license_number' => ['nullable', 'string'],
            'license_plate'  => ['nullable', 'string'],
        ];
    }
}