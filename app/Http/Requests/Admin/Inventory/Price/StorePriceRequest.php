<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Price;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'string', 'uuid', Rule::exists('branches', 'id')->where('is_active', true)],
            'sku_id' => ['required', 'string', 'uuid', Rule::exists('skus', 'id')->where('is_active', true)],
            'type' => ['required', 'string', Rule::in(['REGULAR', 'WHOLESALE', 'PROMOTION', 'DISTRIBUTOR'])],
            'list_price' => ['required', 'numeric', 'min:0.00'],
            'final_price' => [
                'required', 
                'numeric', 
                'min:0.00', 
                'lte:list_price' // Exigencia matemática fundamental: El precio final no puede superar al de lista
            ],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'priority' => ['required', 'integer', 'min:1'],
            'valid_from' => ['required', 'date_format:Y-m-d H:i:s'],
            'valid_to' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:valid_from'],
        ];
    }
}