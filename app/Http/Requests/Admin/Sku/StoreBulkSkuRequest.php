<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Sku;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkSkuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'skus'                    => ['required', 'array', 'min:1', 'max:50'],
            'skus.*.name'             => ['required', 'string', 'max:255'],
            'skus.*.code'             => ['nullable', 'string', 'max:50'],
            'skus.*.price'            => ['required', 'numeric', 'min:0'],
            'skus.*.conversionFactor' => ['required', 'numeric', 'min:0.001'],
            'skus.*.weight'           => ['required', 'numeric', 'min:0'],
            'skus.*.isActive'         => ['required', 'boolean'],
        ];
    }
}