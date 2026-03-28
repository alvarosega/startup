<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class ValidateStep1Request extends FormRequest
{
    use ValidatesGlobalIdentity;

    /**
     * Dictamen: Si omites authorize(), Laravel puede bloquear la petición por defecto 
     * en configuraciones estrictas.
     */
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
            'country_code' => ['required', 'string', 'size:2'], // Faltaba mapear el país
            'email'        => $this->globalEmailRules(),
            'phone'        => $this->globalPhoneRules(),
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}