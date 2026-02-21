<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $catId = $this->route('category')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id', 'different:id'], // No ser padre de sí mismo
            'external_code' => ['nullable', 'string', 'max:50', Rule::unique('categories')->ignore($catId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($catId)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }
}