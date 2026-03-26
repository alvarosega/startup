<?php 

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // EXTRACCIÓN SEGURA: Maneja tanto el objeto como el UUID directamente
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'name' => [
                'required', 'string', 'max:255', 
                // LA LEY: Ignorar el ID actual para permitir "guardar sin cambios de nombre"
                Rule::unique('products', 'name')->ignore($productId)->whereNull('deleted_at')
            ],
            'brand_id'    => ['required', 'uuid', Rule::exists('brands', 'id')->whereNull('deleted_at')],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'   => ['boolean'],
            'is_alcoholic'=> ['boolean'],
        ];
    }
}