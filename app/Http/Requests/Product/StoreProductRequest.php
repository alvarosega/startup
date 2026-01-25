<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'is_alcoholic' => ['boolean'],
            
            // Validación de SKUs anidados
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.name' => ['required', 'string', 'max:255'],
            'skus.*.code' => ['nullable', 'string', 'max:50', 'distinct'], // distinct checa duplicados en el array
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'skus.*.weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}