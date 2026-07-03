<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Profiles;

use Illuminate\Foundation\Http\FormRequest;

final class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'alias' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'branch_id' => ['nullable', 'uuid'],
            'details' => ['nullable', 'string', 'max:255'],
        ];
    }
}