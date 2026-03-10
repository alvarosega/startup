<?php

namespace App\Http\Requests\Driver\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Validation\Rules\Password;

class ValidateStep1Request extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            // SOLO VALIDAMOS LO DEL PASO 1
            'phone'      => $this->globalPhoneRules(), 
            'email'      => $this->globalEmailRules(), 
            'password'   => ['required', 'confirmed', Password::defaults()],
        ];
    }
}