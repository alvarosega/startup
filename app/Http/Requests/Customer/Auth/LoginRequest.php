<?php

namespace App\Http\Requests\Customer\Auth;
use App\Traits\ValidatesGlobalIdentity;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use ValidatesGlobalIdentity;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // <--- IMPORTANTE: Si está en false, da error 403
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData(); // <--- USAR NORMALIZACIÓN CENTRALIZADA
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
            'guest_client_uuid' => ['nullable', 'string'],
        ];
    }
}