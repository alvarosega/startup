<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Sku;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkSkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skus'                     => ['required', 'array', 'min:1', 'max:50'],
            'skus.*.name'              => ['required', 'string', 'max:255'],
            'skus.*.code'              => ['nullable', 'string', 'max:50'],
            'skus.*.base_price'        => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:0.001'],
            'skus.*.weight'            => ['required', 'numeric', 'min:0'],
            'skus.*.is_active'         => ['required', 'boolean'],
        ];
    }
}