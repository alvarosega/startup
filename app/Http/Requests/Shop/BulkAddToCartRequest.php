<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku_id' => ['required', 'exists:skus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}