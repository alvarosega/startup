<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'provider_id' => ['required', 'exists:providers,id'],
            'document_number' => ['required', 'string', 'max:50'],
            'purchase_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:CASH,CREDIT'],
            'payment_due_date' => ['nullable', 'date', 'required_if:payment_type,CREDIT'],
            'notes' => ['nullable', 'string'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'exists:skus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.expiration_date' => ['nullable', 'date'],
        ];

        // Solo validamos branch_id si NO es branch_admin (el DTO se encarga de forzarlo si lo es)
        if (!$this->user()->hasRole('branch_admin')) {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        }

        return $rules;
    }
}