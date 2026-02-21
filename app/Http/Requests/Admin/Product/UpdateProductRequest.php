<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->route('product')->id)->whereNull('deleted_at')],
            'brand_id'     => ['required', 'uuid', 'exists:brands,id'],
            'category_id'  => ['required', 'uuid', 'exists:categories,id'],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_active'    => ['boolean'],
            'is_alcoholic' => ['boolean'],
        ];
    }
}