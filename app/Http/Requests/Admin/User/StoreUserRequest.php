<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        if ($this->has('phone')) {
            $phone = str_replace(' ', '', $this->phone);
            if (!str_starts_with($phone, '+')) {
                $this->merge(['phone' => '+591' . $phone]);
            } else {
                $this->merge(['phone' => $phone]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'unique:users,phone'],
            'email'      => ['nullable', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:6'],
            'role_id'    => ['required', 'exists:roles,id'],
            'branch_id'  => ['nullable', 'exists:branches,id'],
            'is_active'  => ['boolean'],
        ];
    }
}