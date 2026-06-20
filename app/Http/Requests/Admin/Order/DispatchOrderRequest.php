<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class DispatchOrderRequest extends FormRequest
{
    public function authorize(): bool { return auth()->guard('super_admin')->check(); }

    public function rules(): array
    {
        return [
            // Validamos que el admin escriba el PIN de 5 dígitos que le dicta el driver
            'pickup_otp' => ['required', 'string', 'size:5', 'regex:/^[0-9]+$/'], 
        ];
    }
    
    public function messages(): array
    {
        return [
            'pickup_otp.required' => 'Debe ingresar el PIN de recogida provisto por el conductor.',
            'pickup_otp.size' => 'El PIN de recogida debe tener exactamente 5 dígitos.',
        ];
    }
}