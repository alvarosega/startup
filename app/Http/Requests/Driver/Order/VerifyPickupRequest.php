<?php

declare(strict_types=1);

namespace App\Http\Requests\Driver\Order;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPickupRequest extends FormRequest
{
    public function authorize(): bool { return auth()->guard('driver')->check(); }

    public function rules(): array
    {
        return [
            'pickup_otp' => ['required', 'string', 'size:5'],
        ];
    }
}