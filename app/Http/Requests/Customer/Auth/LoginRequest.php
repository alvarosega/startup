<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class LoginRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        // Dictamen: Normalizar el teléfono (+591...) antes de procesar la regla
        $this->normalizeIdentityData(); 
    }

    public function rules(): array
    {
        return [
            'phone'             => ['required', 'string'],
            'password'          => ['required', 'string'],
            'remember'          => ['boolean'],
            'guest_client_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }
}