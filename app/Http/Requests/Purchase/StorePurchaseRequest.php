<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'provider_id' => ['required', 'exists:providers,id'],
            'document_number' => ['required', 'string', 'max:50'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            
            // Validación del Array de Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'exists:skus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'], // Cantidad FINAL en unidades base
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.expiration_date' => ['nullable', 'date', 'after:today'], // Opcional: validar que no esté vencido
        ];
    }

    public function attributes()
    {
        return [
            'items.*.sku_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'document_number' => 'nro de factura'
        ];
    }
}