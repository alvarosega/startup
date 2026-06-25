<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Catalog\Category;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Restringe el transporte HTTP únicamente a usuarios autorizados con el rol de super_admin.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    /**
     * Sanea y prepara los campos de slugs de forma automatizada previa validación.
     */
    protected function prepareForValidation(): void
    {
        if ($this->name && !$this->slug) {
            $this->merge(['slug' => Str::slug($this->name)]);
        } elseif ($this->slug) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        $catId = $this->route('category') instanceof Category 
            ? $this->route('category')->id 
            : $this->route('category');

        return [
            'name'               => ['required', 'string', 'max:100'],
            'slug'               => ['required', 'string', 'max:120', Rule::unique('categories')->ignore($catId)->where('deleted_epoch', 0)],
            'external_code'      => ['nullable', 'string', 'max:50', Rule::unique('categories')->ignore($catId)->where('deleted_epoch', 0)],
            'tax_classification' => ['nullable', 'string', 'max:50'],
            'requires_age_check' => ['required', 'boolean'],
            'is_active'          => ['required', 'boolean'],
            'is_featured'        => ['required', 'boolean'],
            'bg_color'           => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'image'              => ['nullable', 'image', 'max:2048'],
            'icon'               => ['nullable', 'image', 'max:512'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'seo_title'          => ['nullable', 'string', 'max:60'],
            'seo_description'    => ['nullable', 'string', 'max:160'],
            'parent_id'          => [
                'nullable', 
                'string', 
                Rule::exists('categories', 'id')->whereNull('parent_id'),
                function ($attribute, $value, $fail) use ($catId) {
                    if ($value === $catId) {
                        $fail('Operación inválida: Una categoría no puede ser hija de sí misma.');
                    }
                    if ($value && Category::where('parent_id', $catId)->exists()) {
                        $fail('Restricción de nivel: Esta categoría ya posee subcategorías asignadas y no puede descender en la jerarquía.');
                    }
                }
            ],
        ];
    }
}