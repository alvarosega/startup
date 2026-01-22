<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Branch es nullable porque el backend lo puede inyectar para Branch Admin
            'branch_id' => ['nullable', 'exists:branches,id'], 
            'provider_id' => ['required', 'exists:providers,id'],
            'document_number' => ['required', 'string', 'max:50'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            
            // --- VALIDACIÓN FINANCIERA ---
            'payment_type' => ['required', 'in:CASH,CREDIT'],
            // Si es Crédito, la fecha de vencimiento es obligatoria
            'payment_due_date' => ['required_if:payment_type,CREDIT', 'nullable', 'date', 'after_or_equal:purchase_date'],

            // --- DETALLES ---
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'exists:skus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.expiration_date' => ['nullable', 'date', 'after:today'],
        ];
    }
}