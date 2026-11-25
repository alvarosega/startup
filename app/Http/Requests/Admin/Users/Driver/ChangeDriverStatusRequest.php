<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users\Driver;

use Illuminate\Foundation\Http\FormRequest;

class ChangeDriverStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'           => ['required', 'string', 'in:pending,approved,rejected,suspended'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
            'branch_id'        => ['nullable', 'string', 'exists:branches,id'],
        ];
    }
}