<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Sku;

use Illuminate\Foundation\Http\FormRequest;

class GetSkuStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku_id'      => ['required', 'string', 'exists:skus,id'],
            'qty_in_cart' => ['nullable', 'integer', 'min:0', 'max:99']
        ];
    }
}