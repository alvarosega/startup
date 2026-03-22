<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // Generación automática de Slug si el form no lo envía (solo útil en Create, pero blindado aquí)
        if ($this->name && !$this->slug) {
            $this->merge(['slug' => Str::slug($this->name)]);
        } elseif ($this->slug) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        $catId = $this->route('category')?->id;

        return [
            'name'               => ['required', 'string', 'max:100'],
            'slug'               => ['required', 'string', 'max:120', Rule::unique('categories')->ignore($catId)->withoutTrashed()],
            // Corregido: parent_id debe permitir NULL real
            'parent_id'          => ['nullable', 'uuid', Rule::exists('categories', 'id')->withoutTrashed()],
            'external_code'      => ['nullable', 'string', 'max:50', Rule::unique('categories')->ignore($catId)->withoutTrashed()],
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['boolean'],
            'is_active'          => ['boolean'],
            'is_featured'        => ['boolean'],
            'sort_order'         => ['integer', 'min:0'],
            'bg_color'           => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'image'              => ['nullable', 'image', 'max:2048'],
            'icon'               => ['nullable', 'image', 'max:512'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'seo_title'          => ['nullable', 'string', 'max:60'],
            'seo_description'    => ['nullable', 'string', 'max:160'],
        ];
    }
}