<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productId = $this->route('product'); // ID del producto en la URL

        return [
            // 1. Validación del Padre (Producto)
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($productId)],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_alcoholic' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'], // Si envían foto nueva

            // 2. Validación de Hijos (SKUs)
            'skus' => ['required', 'array', 'min:1'], // Al menos 1 SKU siempre
            
            // ID del SKU: Si viene, debe existir en la tabla skus. Si es null, es nuevo.
            'skus.*.id' => ['nullable', 'integer', 'exists:skus,id'],
            
            'skus.*.name' => ['required', 'string', 'max:100'],
            'skus.*.code' => ['nullable', 'string', 'distinct'], // 'distinct' valida duplicados en el array enviado
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'skus.*.weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}