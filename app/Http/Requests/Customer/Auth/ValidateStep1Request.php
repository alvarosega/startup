<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;

class ValidateStep1Request extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation() 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => $this->globalPhoneRules(),
            'email'      => $this->globalEmailRules(),
            'password'   => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ];
    }
}