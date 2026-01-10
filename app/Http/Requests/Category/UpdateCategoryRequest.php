<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $categoryId = $this->route('category') instanceof Category 
            ? $this->route('category')->id 
            : $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            
            'parent_id' => [
                'nullable', 
                'exists:categories,id',
                // No ser padre de mí mismo
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value == $categoryId) {
                        $fail('Una categoría no puede ser su propia madre.');
                    }
                    // Validar profundidad (2 niveles)
                    if ($value) {
                        $parent = Category::find($value);
                        if ($parent && $parent->parent_id !== null) {
                            $fail('Solo se permiten 2 niveles de profundidad.');
                        }
                    }
                },
            ],
            
            // Ignorar el código propio en validación unique
            'external_code' => [
                'nullable', 'string', 'max:50', 
                Rule::unique('categories', 'external_code')->ignore($categoryId)
            ],
            
            'image' => ['nullable', 'image', 'max:2048'],
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