<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Resolución segura de UUID independientemente del Route Binding
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : $product;

        return [
            'name' => [
                'required', 'string', 'max:255', 
                Rule::unique('products', 'name')->ignore($productId)->whereNull('deleted_at')
            ],
            'brand_id' => [
                'required', 'uuid', 
                Rule::exists('brands', 'id')->whereNull('deleted_at')
            ],
            'category_id' => [
                'required', 'uuid', 
                Rule::exists('categories', 'id')->whereNull('deleted_at')
            ],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'    => ['boolean'],
            'is_alcoholic' => ['boolean'],
        ];
    }
}