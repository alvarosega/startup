<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // En update, el ID viene en la ruta
        $productId = $this->route('product'); 
        // Nota: Si usas Route Model Binding, $productId es el objeto Product. 
        // Si pasas solo ID, es string/int. Asumimos objeto o ID.
        $id = is_object($productId) ? $productId->id : $productId;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'is_alcoholic' => ['boolean'],
            
            'skus' => ['required', 'array', 'min:1'],
            'skus.*.id' => ['nullable', 'uuid'], // ID opcional para SKUs nuevos
            'skus.*.name' => ['required', 'string', 'max:255'],
            'skus.*.code' => ['nullable', 'string', 'max:50'], // Quitamos unique estricto aquí para simplificar validación array
            'skus.*.price' => ['required', 'numeric', 'min:0'],
            'skus.*.conversion_factor' => ['required', 'numeric', 'min:1'],
            'skus.*.weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}