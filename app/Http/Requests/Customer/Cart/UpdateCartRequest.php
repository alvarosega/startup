<?php

namespace App\Http\Requests\Customer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // No solo validamos que sea entero, sino que no sea cero.
            'quantity' => ['required', 'integer', 'min:1', 'max:99']
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'La cantidad mínima es 1 unidad.',
            'quantity.max' => 'Para compras mayores a 99 unidades, contacte a ventas.',
        ];
    }
}