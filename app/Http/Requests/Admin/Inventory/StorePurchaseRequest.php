<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

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
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'provider_id' => ['required', 'uuid', 'exists:providers,id'],
            'document_number' => [
                'required', 
                'string', 
                'max:32',
                Rule::unique('purchases', 'document_number')->where(function ($query) {
                    return $query->where('deleted_epoch', 0);
                })
            ],
            'purchase_date' => ['required', 'date', 'date_format:Y-m-d'],
            'payment_type' => ['required', 'string', 'in:CASH,CREDIT'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'uuid', 'exists:skus,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.lot_code' => ['required', 'string', 'max:32', 'alpha_num'],
            'items.*.expiration_date' => ['nullable', 'date', 'date_format:Y-m-d']
        ];
    }
}