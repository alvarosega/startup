<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ya protegemos con middleware en rutas
    }

    // 1. OPCIÓN C: Inyección Forzosa antes de validar
    protected function prepareForValidation()
    {
        // Si es Branch Admin, le incrustamos SU sucursal a la fuerza.
        // Ignoramos lo que venga del formulario.
        if ($this->user()->hasRole('branch_admin')) {
            $this->merge([
                'branch_id' => $this->user()->branch_id
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'is_active' => 'boolean',
        ];

        // 2. Validación de Sucursal
        if ($this->user()->hasRole('super_admin')) {
            $rules['branch_id'] = 'required|exists:branches,id';
        } else {
            // Para el resto, el ID ya fue inyectado en prepareForValidation, 
            // solo verificamos que exista (y que coincida con el suyo implícitamente)
            $rules['branch_id'] = 'required|exists:branches,id'; 
        }

        // 3. OPCIÓN B: Restricción de Roles (Anti-Escalada)
        if ($this->user()->hasRole('branch_admin')) {
            // Solo permite roles operativos (Inventario, Operador, etc.)
            // PROHIBIDO: super_admin, branch_admin, finance_manager, logistics_manager
            $allowedRoles = ['inventory_manager', 'logistics_operator', 'growth_specialist', 'identity_auditor'];
            
            $rules['role_id'] = ['required', Rule::exists('roles', 'id')->whereIn('name', $allowedRoles)];
        } else {
            // Super Admin puede crear todo excepto otro Super Admin (opcional)
            $rules['role_id'] = 'required|exists:roles,id';
        }

        return $rules;
    }
}