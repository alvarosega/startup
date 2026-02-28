<?php

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 1. Capa Logística
            'delivery_type' => ['required', 'string', Rule::in(['pickup', 'delivery'])],
            
            'address_id' => [
                Rule::requiredIf($this->delivery_type === 'delivery'),
                'nullable',
                'string',
                Rule::exists('customer_addresses', 'id')->where(function ($query) {
                    $query->where('customer_id', auth()->guard('customer')->id());
                }),
            ],

            // 2. Capa Financiera
            'payment_type' => ['required', 'string', Rule::in(['total', 'partial'])],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_type.required' => 'Debes seleccionar un método de entrega.',
            'delivery_type.in' => 'El método de entrega seleccionado no es válido.',
            'address_id.required_if' => 'Debes seleccionar una dirección para el envío a domicilio.',
            'address_id.exists' => 'La dirección seleccionada no es válida o no te pertenece.',
            'payment_type.required' => 'Debes elegir tu modalidad de pago (Total o Parcial).',
            'payment_type.in' => 'La modalidad de pago seleccionada es inválida.',
        ];
    }
}