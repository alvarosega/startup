<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Purchase;

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
            'branch_id'       => ['required', 'uuid', Rule::exists('branches', 'id')->where('deleted_epoch', 0)],
            'provider_id'     => ['required', 'uuid', Rule::exists('providers', 'id')->where('deleted_epoch', 0)],
            'document_number' => [
                'required', 
                'string', 
                'max:32', 
                Rule::unique('purchases', 'document_number')->where('deleted_epoch', 0) // Evitar duplicidad de facturas activas
            ],
            'purchase_date'   => ['required', 'date', 'before_or_equal:today'], // No se pueden registrar compras a futuro
            'payment_type'    => ['required', 'string', Rule::in(['CASH', 'CREDIT'])],
            'lot_code'        => ['required', 'string', 'max:32'],
            'expiration_date' => ['nullable', 'date', 'after:purchase_date'], // El lote debe expirar después de la fecha de compra
            
            // Validación de la estructura matricial de artículos
            'items'           => ['required', 'array', 'min:1'],
            'items.*.sku_id'  => ['required', 'uuid', Rule::exists('skus', 'id')],
            'items.*.quantity'=> ['required', 'numeric', 'min:0.001'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('lot_code')) {
            $this->merge([
                'lot_code' => mb_toUpperCase(trim((string) $this->lot_code))
            ]);
        }
    }
}