<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Aquí podrías agregar lógica de roles (ej: solo admin o manager)
        // Por ahora lo dejamos en true y confiamos en el Middleware de rutas
        return true; 
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending_proof,review,confirmed,dispatched,completed,cancelled',
            'rejection_reason' => 'required_if:status,cancelled|nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'rejection_reason.required_if' => 'Debes indicar el motivo del rechazo.',
        ];
    }
}