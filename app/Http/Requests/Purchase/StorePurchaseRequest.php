<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'provider_id' => 'required|exists:providers,id',
            'document_number' => 'required|string|max:50',
            'purchase_date' => 'required|date',
            
            'payment_type' => ['required', Rule::in(['CASH', 'CREDIT'])],
            // Si es crédito, la fecha de vencimiento es obligatoria y debe ser posterior a hoy
            'payment_due_date' => 'nullable|required_if:payment_type,CREDIT|date|after_or_equal:purchase_date',
            
            'notes' => 'nullable|string|max:1000',
            
            // Validación de Items
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity_input' => 'required|integer|min:1',
            'items.*.total_cost_input' => 'required|numeric|min:0',
            'items.*.expiration_date' => 'nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_due_date.required_if' => 'La fecha de vencimiento es obligatoria para compras a crédito.',
            'items.*.sku_id.required' => 'Seleccione un producto para cada línea.',
            'items.*.quantity_input.min' => 'La cantidad debe ser mayor a 0.',
        ];
    }
}