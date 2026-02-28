<?php

namespace App\Http\Requests\Driver\Auth;

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
        // Normaliza el teléfono (+XXXXXXXX) antes de intentar autenticar
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}