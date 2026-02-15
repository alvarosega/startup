<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;
use App\Traits\ValidatesGlobalIdentity;

class StoreUserRequest extends FormRequest
{   use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'string', 'min:6'],
            'role_id'    => ['required'], 
            'email'      => $this->globalEmailRules(),
            'phone'      => $this->globalPhoneRules()
        ];

        $role = Role::find($this->input('role_id'));
        $isCustomer = $role && $role->name === 'customer';

        if ($isCustomer) {
            $rules['email'] = ['required', 'email', 'unique:customers,email'];
            $rules['phone'] = ['required', 'string', 'unique:customers,phone'];
            $rules['latitude']  = ['required', 'numeric'];
            $rules['longitude'] = ['required', 'numeric'];
            $rules['address']   = ['required', 'string'];
            $rules['branch_id'] = ['nullable', 'string', 'size:32']; 
        } elseif ($isDriver) {
        // REGLAS PARA CONDUCTORES
        $rules['email'] = ['required', 'email', 'unique:drivers,email'];
        $rules['phone'] = ['required', 'string', 'unique:drivers,phone'];
        $rules['branch_id'] = ['nullable'];
        }else {
            $rules['email'] = ['required', 'email', 'unique:admins,email'];
            $rules['phone'] = ['required', 'string', 'unique:admins,phone'];
            // Para el Staff, la sucursal es obligatoria
            $rules['branch_id'] = ['required', 'string', 'size:32'];
        }

        return $rules;
    }
}