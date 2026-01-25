<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        // Obtenemos el usuario de la ruta para ignorar su ID
        $userId = $this->route('user')->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', Rule::unique('users', 'phone')->ignore($userId)],
            'email'      => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'   => ['nullable', 'string', 'min:6'], // Opcional al editar
            'role_id'    => ['required', 'exists:roles,id'],
            'branch_id'  => ['nullable', 'exists:branches,id'],
            'is_active'  => ['boolean'],
        ];
    }
}