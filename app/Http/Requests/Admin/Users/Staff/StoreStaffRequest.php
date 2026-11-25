<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('super_admin');
        return $user?->hasRole('super_admin') || ($user?->can('manage_users') ?? false);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'email'      => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')],
            'password'   => ['required', 'string', 'min:8'],
            'branch_id'  => [
                'nullable',
                Rule::requiredIf(fn () => !in_array('super_admin', (array) $this->input('roles', []))),
                Rule::exists('branches', 'id')->where('is_active', true),
            ],
            'is_active'     => ['boolean'],
            'roles'         => ['required', 'array', 'min:1'],
            'roles.*'       => ['string', Rule::exists('roles', 'name')->where('guard_name', 'super_admin')],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'super_admin')],
        ];
    }
}
