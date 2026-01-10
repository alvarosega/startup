<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Category;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            
            // REGLA DE 2 NIVELES:
            // Si elijo un padre, ese padre NO debe tener padre (debe ser raíz).
            'parent_id' => [
                'nullable', 
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = Category::find($value);
                        if ($parent && $parent->parent_id !== null) {
                            $fail('Solo se permiten 2 niveles de profundidad (Categoría > Subcategoría).');
                        }
                    }
                }
            ],
            
            'external_code' => ['nullable', 'string', 'max:50', 'unique:categories,external_code'],
            'image' => ['nullable', 'image', 'max:2048'], // Validación del archivo entrante
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}