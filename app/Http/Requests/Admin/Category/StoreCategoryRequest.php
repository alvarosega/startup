<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name'               => ['required', 'string', 'max:255'],
            'external_code'      => ['nullable', 'string', 'max:50', Rule::unique('categories')->ignore($categoryId)->withoutTrashed()],
            'slug'               => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($categoryId)->withoutTrashed()],
            'description'        => ['nullable', 'string'],
            'image'              => ['nullable', 'image', 'max:2048'],
            'icon'               => ['nullable', 'image', 'max:1024'],
            'bg_color'           => ['nullable', 'string', 'max:7'],
            'seo_title'          => ['nullable', 'string', 'max:255'],
            'seo_description'    => ['nullable', 'string'],
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['boolean'],
            'is_active'          => ['boolean'],
            'is_featured'        => ['boolean'],
        ];
    }
}