<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Nos aseguramos que el email exista EXCLUSIVAMENTE en el silo customer
            'email' => ['required', 'email', 'exists:customers,email']
        ];
    }
}