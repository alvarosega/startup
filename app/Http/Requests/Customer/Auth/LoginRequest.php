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
        // 1. Normalización de identidad (Trait)
        $this->normalizeIdentityData();

        // 2. RECUPERACIÓN DE EMERGENCIA: Si el body no trae el UUID, lo inyectamos de la sesión
        // Esto garantiza que el DTO 'LoginCustomerData' siempre tenga el rastro para la fusión.
        if (!$this->filled('guest_client_uuid')) {
            $this->merge([
                'guest_client_uuid' => session('guest_client_uuid')
            ]);
        }
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