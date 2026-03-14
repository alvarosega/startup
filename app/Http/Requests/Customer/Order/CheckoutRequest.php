<?php

namespace App\Http\Requests\Customer\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo clientes autenticados pueden procesar un checkout
        return auth()->guard('customer')->check();
    }

    /**
     * Reglas de validación atómicas.
     */
    public function rules(): array
    {
        return [
            'delivery_type' => [
                'required', 
                Rule::in(['pickup', 'delivery'])
            ],
            
            // El address_id ya no es obligatorio para el cálculo (usamos Customer table)
            // pero si se envía, validamos que exista y pertenezca al cliente.
            'address_id' => [
                'nullable',
                'string',
                Rule::exists('customer_addresses', 'id')->where(function ($query) {
                    $query->where('customer_id', auth()->guard('customer')->id());
                }),
            ],

            'payment_method' => [
                'required',
                Rule::in(['qr']) // Expandible en el futuro a 'transfer', 'card'
            ],
        ];
    }

    /**
     * Mensajes personalizados para una UX superior.
     */
    public function messages(): array
    {
        return [
            'delivery_type.required' => 'Debes seleccionar si prefieres envío o retiro.',
            'delivery_type.in'       => 'El método de entrega seleccionado no es válido.',
            'address_id.exists'      => 'La dirección seleccionada no es válida o no te pertenece.',
            'payment_method.required' => 'Debes seleccionar un método de pago.',
        ];
    }
}