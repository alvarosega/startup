<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'branch_id'             => ['required', 'string', 'uuid', Rule::exists('branches', 'id')->where('is_active', true)],
            'provider_id'           => ['required', 'string', 'uuid', Rule::exists('providers', 'id')->where('is_active', true)],
            'document_number'       => ['required', 'string', 'alpha_num', 'max:32'],
            'purchase_date'         => ['required', 'date', 'before_or_equal:today'],
            'payment_type'          => ['required', 'string', Rule::in(['CASH', 'CREDIT'])],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.sku_id'        => ['required', 'string', 'uuid', Rule::exists('skus', 'id')->where('is_active', true)],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.001'],
            'items.*.cost_price'    => ['required', 'numeric', 'min:0.00'],
            'items.*.lot_code'      => ['nullable', 'string', 'max:32'],
            'items.*.expiration_date'=> ['nullable', 'date', 'after:purchase_date'],
        ];
    }
}