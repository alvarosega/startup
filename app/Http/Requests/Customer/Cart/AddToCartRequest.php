<?php

namespace App\Http\Requests\Customer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku_id' => [
                'required', 
                'string', 
                'exists:skus,id,is_active,1' // Solo permite SKUs activos
            ], 
            'quantity'          => ['required', 'integer', 'min:1'],
            'guest_client_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku_id.exists' => 'El producto seleccionado no es válido o no existe.',
            'quantity.min'  => 'La cantidad mínima debe ser al menos 1.',
        ];
    }
}