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
            'phone'    => $this->globalPhoneRules('drivers'), 
            'email'    => $this->globalEmailRules('drivers'), 
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}