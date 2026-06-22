<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Sku;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $skuId = $this->route('sku')?->id ?? $this->route('sku');

        return [
            'name'              => ['required', 'string', 'max:255'],
            'code'              => ['required', 'string', 'max:50', Rule::unique('skus', 'code')->ignore($skuId)->where('deleted_epoch', 0)],
            'base_price'        => ['required', 'numeric', 'min:0'],
            'conversion_factor' => ['required', 'numeric', 'min:0.001'],
            'weight'            => ['required', 'numeric', 'min:0'],
            'is_active'         => ['required', 'boolean'],
            'image'             => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
        ];
    }
}