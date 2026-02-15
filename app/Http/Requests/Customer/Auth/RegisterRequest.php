<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Traits\ValidatesGlobalIdentity; // <--- AÑADIR

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; }

    protected function prepareForValidation() 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            // Identidad Global
            'phone' => $this->globalPhoneRules(),
            'email' => $this->globalEmailRules(),
            
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms'    => ['accepted'],
            
            // Datos de Dirección
            'alias'     => ['nullable', 'string', 'max:50'],
            'address'   => ['required', 'string', 'max:255'],
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            
            // Branch ID (Hex de 32 chars)
            'branch_id' => ['nullable', 'string', 'size:32'], 
            
            // Avatar
            'avatar_type'   => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'],
        ];
    }
}