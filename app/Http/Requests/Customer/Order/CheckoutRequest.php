<?php

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'delivery_type' => ['required', Rule::in(['pickup', 'delivery'])],
            // Si es delivery, el address_id es obligatorio y debe existir para el cliente
            'address_id'    => [
                'required_if:delivery_type,delivery', 
                'nullable', 
                'string', 
                Rule::exists('customer_addresses', 'id')->where('customer_id', auth()->id())
            ],
            'payment_method' => ['required', Rule::in(['qr'])] // Por ahora solo QR
        ];
    }
}