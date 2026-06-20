<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    public function rules(): array
    {
        return [
            'delivery_type' => ['required', Rule::in(['pickup', 'delivery'])],
            'payment_method' => ['required', Rule::in(['qr'])], // Extensible a 'card', 'cash' en el futuro
        ];
    }
}