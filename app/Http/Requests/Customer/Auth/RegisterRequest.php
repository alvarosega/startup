<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Traits\ValidatesGlobalIdentity; // <--- AÑADIR

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();

        // INTEGRIDAD: Recuperar UUID de la sesión si el frontend no lo envió
        if (!$this->filled('guest_client_uuid')) {
            $this->merge([
                'guest_client_uuid' => session('guest_client_uuid')
            ]);
        }
    }
    public function rules(): array
    {
        return [
            'phone'             => $this->globalPhoneRules(), 
            'email'             => $this->globalEmailRules(), 
            'password'          => ['required', 'confirmed', 'min:8'],
            'first_name'        => ['required', 'string', 'max:100'],
            'last_name'         => ['required', 'string', 'max:100'],
            'address'           => ['required', 'string'],
            'country_code'      => ['required', 'string', 'max:3'],
            'latitude'          => ['required', 'numeric'],
            'longitude'         => ['required', 'numeric'],
            'avatar_type'       => ['required', 'string'],
            'avatar_source'     => ['nullable', 'string'],
            'avatar_file'       => ['nullable', 'image', 'max:2048'],
            'alias'             => ['nullable', 'string'],
            'details'           => ['nullable', 'string'],
            'guest_client_uuid' => ['nullable', 'string', 'uuid'],
            'branch_id'         => ['nullable', 'uuid', 'exists:branches,id'], 
        ];
    }
}