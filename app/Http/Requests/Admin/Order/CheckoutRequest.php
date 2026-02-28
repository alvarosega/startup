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
            'delivery_type' => ['required', 'string', Rule::in(['pickup', 'delivery'])],
            
            // Validaciones del objeto JSON
            'delivery_data' => ['required', 'array'],
            'delivery_data.phone' => ['required', 'string', 'min:8', 'max:20'],
            
            // Requerido solo si es delivery
            'delivery_data.address' => [
                Rule::requiredIf($this->input('delivery_type') === 'delivery'), 
                'nullable', 
                'string', 
                'max:255'
            ],
            'delivery_data.reference' => ['nullable', 'string', 'max:255'],
            'delivery_data.latitude' => ['nullable', 'numeric'],
            'delivery_data.longitude' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_data.address.required' => 'La dirección es obligatoria para envíos a domicilio.',
            'delivery_data.phone.required' => 'Un número de contacto es obligatorio.',
        ];
    }
}