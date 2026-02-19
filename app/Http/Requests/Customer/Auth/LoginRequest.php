<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // <--- IMPORTANTE: Si está en false, da error 403
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = $this->phone;
            
            // Eliminamos todo excepto números y el signo +
            $cleanPhone = preg_replace('/[^\+0-9]/', '', $phone);
    
            // Si el usuario olvidó el +, pero el número es largo, 
            // podrías intentar inferirlo, pero es mejor confiar en VueTelInput.
            // ELIMINAMOS la lógica de "if !str_starts_with 591"
            
            $this->merge(['phone' => $cleanPhone]);
        }
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
        ];
    }
}