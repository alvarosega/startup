<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;

class CloseDailyCashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'actual_amount' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}