<?php

namespace App\Http\Requests\Price;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sku_id' => ['required', 'exists:skus,id'], // UUID Check
            'branch_id' => ['nullable', 'exists:branches,id'], // ID Numérico Check
            'final_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}