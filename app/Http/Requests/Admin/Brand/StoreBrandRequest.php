<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:brands,name'],
            'provider_id' => ['required', 'uuid', 'exists:providers,id'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'website'     => ['nullable', 'url'],
            'image'       => ['nullable', 'image', 'max:2048'], // 2MB Max
            'is_active'   => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order'  => ['integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}