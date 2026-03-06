<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                // LA LEY: Ignorar registros con SoftDeletes
                Rule::unique('products', 'name')->whereNull('deleted_at')
            ],
            'brand_id' => ['required', 'uuid', 'exists:brands,id'],
            'category_id' => [
                'required', 
                'uuid', 
                // LA LEY: Solo permitir subcategorías (hojas del árbol)
                Rule::exists('categories', 'id')->whereNotNull('parent_id')
            ],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'    => ['boolean'],
            'is_alcoholic' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => '// ERROR: DEBE SELECCIONAR UNA SUBCATEGORÍA VÁLIDA',
            'name.unique' => '// CONFLICTO: ESTE PRODUCTO YA EXISTE EN EL ARCHIVO CENTRAL',
        ];
    }
}