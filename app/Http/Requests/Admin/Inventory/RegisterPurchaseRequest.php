<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización ya se maneja en el orquestador
    }

    public function rules(): array
    {
        return [
            'branch_id'       => ['required', 'uuid', 'exists:branches,id'],
            'provider_id'     => ['required', 'uuid', 'exists:providers,id'],
            'purchase_date'   => ['required', 'date'],
            'payment_type'    => ['required', 'in:CASH,CREDIT'],
            'is_emergency'    => ['required', 'boolean'],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.sku_id'    => ['required', 'uuid', 'exists:skus,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}