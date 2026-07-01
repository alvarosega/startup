<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Auth;

use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'phone' => $this->globalPhoneRules(),
            'email' => $this->globalEmailRules(),
            'password' => ['required', 'string', 'min:8'],
            'country_code' => ['required', 'string', 'max:3'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'branch_id' => ['nullable', 'uuid'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'avatar_type' => ['required', 'string', 'max:50'],
            'avatar_source' => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:255'],
            'guest_client_uuid' => ['nullable', 'uuid']
        ];
    }
}