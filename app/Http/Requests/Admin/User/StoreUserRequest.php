<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;
use App\Traits\ValidatesGlobalIdentity; // <--- AÑADIR

class StoreUserRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; }

    protected function prepareForValidation() 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'string', 'min:6'],
            'role_id'    => ['required'],
            // Aplicamos validación global aquí
            'email'      => $this->globalEmailRules(),
            'phone'      => $this->globalPhoneRules(),
        ];

        $role = Role::find($this->input('role_id'));
        $roleName = $role?->name;

        if ($roleName === 'customer') {
            $rules['latitude']  = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['address']   = ['required', 'string'];
            $rules['branch_id'] = ['nullable', 'string', 'size:32']; 
        } elseif ($roleName === 'driver') {
            $rules['branch_id'] = ['nullable'];
        } else {
            $rules['branch_id'] = ['required', 'string', 'size:32'];
        }

        return $rules;
    }
}