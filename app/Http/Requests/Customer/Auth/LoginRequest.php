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
            // Eliminar espacios, guiones, paréntesis
            $rawPhone = preg_replace('/[^0-9]/', '', $this->phone);
            
            // Agregar prefijo +591 si falta (Lógica de negocio boliviana)
            if (!str_starts_with($rawPhone, '591') && strlen($rawPhone) >= 8) {
                $rawPhone = '591' . $rawPhone;
            }
            
            // Asegurar formato internacional completo
            $phone = '+' . $rawPhone;

            $this->merge(['phone' => $phone]);
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