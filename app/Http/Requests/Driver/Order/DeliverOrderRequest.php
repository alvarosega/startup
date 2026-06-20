<?php

declare(strict_types=1);

namespace App\Http\Requests\Driver\Order;

use Illuminate\Foundation\Http\FormRequest;

class DeliverOrderRequest extends FormRequest
{
    public function authorize(): bool { return auth()->guard('driver')->check(); }

    public function rules(): array
    {
        return [
            // El delivery_otp de nuestra migración es de 4 dígitos
            'delivery_otp' => ['required', 'numeric', 'digits:4'],
        ];
    }
}