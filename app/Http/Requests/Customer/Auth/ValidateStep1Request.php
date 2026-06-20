<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class ValidateStep1Request extends FormRequest
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

    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            // CORRECCIÓN: Unificado a max:3 para evitar asimetrías con el paso final
            'country_code' => ['required', 'string', 'max:3'],
            'email'        => $this->globalEmailRules(),
            'phone'        => $this->globalPhoneRules(),
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}