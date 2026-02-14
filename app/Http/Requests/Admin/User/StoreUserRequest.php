<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 1. Reglas Comunes
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'string', 'min:6'],
            'role_id'    => ['required', 'exists:roles,id'],
        ];

        // 2. Obtener el rol para decidir la validación
        $roleId = $this->input('role_id');
        $role = $roleId ? Role::find($roleId) : null;
        
        // Verificamos si es rol de cliente (asegúrate que el name en DB sea 'customer')
        $isCustomer = $role && $role->name === 'customer';

        if ($isCustomer) {
            // --- REGLAS PARA CLIENTES (Tabla customers) ---
            $rules['email'] = ['required', 'email', 'unique:customers,email'];
            $rules['phone'] = ['required', 'string', 'unique:customers,phone'];
            
            // Ubicación obligatoria (El mapa en el frontend debe enviarlos)
            $rules['latitude']  = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['address']   = ['required', 'string'];
            
            // Branch ID es nullable para clientes (se calcula dinámicamente)
            $rules['branch_id'] = ['nullable']; 

        } else {
            // --- REGLAS PARA STAFF (Tabla admins) ---
            $rules['email'] = ['required', 'email', 'unique:admins,email'];
            $rules['phone'] = ['required', 'string', 'unique:admins,phone'];
            
            // Sucursal obligatoria para Staff (Hex String de 32 chars)
            $rules['branch_id'] = ['required', 'string', 'size:32', 'exists:branches,id'];
        }

        return $rules;
    }
}