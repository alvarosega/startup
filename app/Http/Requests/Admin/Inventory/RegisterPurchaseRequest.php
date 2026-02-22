<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPurchaseRequest extends FormRequest
{
    /**
     * Solo el guard super_admin puede disparar esta validación.
     */
    public function authorize(): bool
    {
        return auth()->guard('super_admin')->check();
    }

    /**
     * Reglas de validación estrictas.
     */
    public function rules(): array
    {
        return [
            'branch_id'       => ['required', 'uuid', 'exists:branches,id'],
            'provider_id'     => ['required', 'uuid', 'exists:providers,id'],
            'document_number' => ['required', 'string', 'max:50'],
            'purchase_date'   => ['required', 'date', 'before_or_equal:today'],
            'payment_type'    => ['required', 'string', Rule::in(['CASH', 'CREDIT', 'TRANSFER'])],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
            
            // Validación de Items
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.sku_id'            => ['required', 'uuid', 'exists:skus,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'         => ['required', 'numeric', 'min:0'],
            'items.*.expiration_date'   => ['nullable', 'date', 'after:purchase_date'],
            'items.*.lot_code'          => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Sanitización y normalización antes de pasar al DTO.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'total_amount' => (float) $this->total_amount,
            'document_number' => strtoupper(trim($this->document_number)),
        ]);
    }

    /**
     * Validación de integridad financiera (Total vs Suma de Items).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sumItems = collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_cost']);
            
            // Margen de error para flotantes (epsilon)
            if (abs($sumItems - (float) $this->total_amount) > 0.01) {
                $validator->errors()->add(
                    'total_amount', 
                    "La suma de los items ({$sumItems}) no coincide con el total declarado ({$this->total_amount})."
                );
            }
        });
    }
}