<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('super_admin')->check();
    }

    public function rules(): array
    {
        return [
            'branch_id'       => ['required', 'uuid', 'exists:branches,id'],
            'provider_id'     => ['required', 'uuid', 'exists:providers,id'],
            'document_number' => ['required', 'string', 'max:50'],
            'purchase_date'   => ['required', 'date', 'before_or_equal:today'],
            'payment_type'    => ['required', Rule::in(['CASH', 'CREDIT', 'TRANSFER'])],
            'payment_due_date'=> ['required_if:payment_type,CREDIT', 'nullable', 'date', 'after_or_equal:purchase_date'],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
            
            // Items
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.sku_id'          => ['required', 'uuid', 'exists:skus,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'       => ['required', 'numeric', 'min:0'],
            'items.*.expiration_date' => ['nullable', 'date', 'after:purchase_date'],
            'items.*.lot_code'        => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sum = collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_cost']);
            if (abs($sum - $this->total_amount) > 0.01) {
                $validator->errors()->add('total_amount', "La suma de ítems ($sum) no coincide con el total.");
            }
        });
    }
}