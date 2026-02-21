<?php
namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255', 'unique:products,name'],
            'brand_id'     => ['required', 'uuid', 'exists:brands,id'],
            'category_id'  => ['required', 'uuid', 'exists:categories,id'],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'], // 2MB max
            'is_active'    => ['boolean'],
            'is_alcoholic' => ['boolean'],
            // YA NO SOLICITAMOS 'skus' AQUÍ
        ];
    }
}