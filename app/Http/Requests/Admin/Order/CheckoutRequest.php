<?php

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    public function rules(): array
    {
        return [
            // El Frontend solo tiene derecho a decidir CÓMO lo quiere, no DÓNDE.
            'delivery_type' => ['required', 'string', Rule::in(['pickup', 'delivery'])],
            
            // Opcional: Si deseas permitir notas para la entrega
            'notes'         => ['nullable', 'string', 'max:500'], 
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_type.required' => 'Debes seleccionar un método de entrega válido.',
            'delivery_type.in'       => 'El método de entrega no está permitido.',
        ];
    }
}