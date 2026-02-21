<?php

namespace App\Http\Requests\Admin\Sku;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'code'              => ['nullable', 'string', Rule::unique('skus')->ignore($this->route('sku')->id)->whereNull('deleted_at')],
            'base_price'        => ['required', 'numeric', 'min:0'],
            'conversion_factor' => ['required', 'integer', 'min:1'],
            'weight'            => ['required', 'numeric', 'min:0'],
            'image'             => ['nullable', 'image', 'max:2048'],
            'is_active'         => ['boolean'],
        ];
    }
}