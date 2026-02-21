<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'], // Check UUID
            'external_code' => ['nullable', 'string', 'max:50', 'unique:categories,external_code'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'children' => ['nullable', 'array'],
            'children.*.name' => ['required', 'string', 'max:255'],
            'children.*.external_code' => ['nullable', 'string', 'max:50'],
            'children.*.image' => ['nullable', 'image', 'max:2048'],
            // SEO & Config
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }
}