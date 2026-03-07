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
                'required', 'string', 'max:255', 
                // LA LEY: Ignorar registros con SoftDeletes
                Rule::unique('products', 'name')->whereNull('deleted_at')
            ],
            'brand_id' => [
                'required', 'uuid', 
                // LA LEY: Asegurar que la marca existe y no está en la papelera
                Rule::exists('brands', 'id')->whereNull('deleted_at')
            ],
            'category_id' => [
                'required', 'uuid', 
                // CORRECCIÓN CRÍTICA: Se eliminó la restricción de subcategoría
                // para que coincida con los datos que envía el Controller (raíces).
                Rule::exists('categories', 'id')->whereNull('deleted_at')
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
            'category_id.exists' => '// ERROR_404: CATEGORÍA INEXISTENTE O DADA DE BAJA',
            'brand_id.exists'    => '// ERROR_404: MARCA INEXISTENTE O DADA DE BAJA',
            'name.unique'        => '// CONFLICTO: ESTE MAESTRO YA EXISTE EN EL SISTEMA VIVO',
        ];
    }
}