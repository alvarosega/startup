<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Producto (Padre)
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            
            // SKUs (Hijos)
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.name' => ['required', 'string', 'max:100'],
            'skus.*.code' => ['nullable', 'string', 'distinct', 'unique:skus,code'], // distinct valida duplicados en el mismo array
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'skus.*.weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'skus.required' => 'Debes agregar al menos una presentación (SKU).',
            'skus.*.code.unique' => 'El código de barras :input ya está registrado en otro producto.',
            'skus.*.code.distinct' => 'No puedes tener códigos de barras duplicados en el formulario.',
        ];
    }
}