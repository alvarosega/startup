<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_type' => ['required', 'in:pickup,delivery'],
            
            'address_id' => [
                'nullable',
                // Requerido solo si es delivery
                'required_if:delivery_type,delivery', 
                
                // Validación de seguridad: debe existir en 'user_addresses' y ser de este usuario
                Rule::exists('user_addresses', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'address_id.required_if' => 'Debes seleccionar una dirección para el envío.',
            'address_id.exists' => 'La dirección seleccionada no es válida.',
        ];
    }
}