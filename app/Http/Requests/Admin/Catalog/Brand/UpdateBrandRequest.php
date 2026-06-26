<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Catalog\Brand;

class UpdateBrandRequest extends FormRequest
{
    /**
     * RECTIFICACIÓN: Blindaje perimetral restrictivo asociado al rol administrativo super_admin.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->name ?? ''),
        ]);
    }

    public function rules(): array
    {
        $brandId = $this->route('brand') instanceof Brand
            ? $this->route('brand')->id
            : $this->route('brand');

        return [
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:120', Rule::unique('brands')->ignore($brandId)->where('deleted_epoch', 0)],
            'provider_id' => ['required', 'string', Rule::exists('providers', 'id')],
            'category_id' => ['required', 'string', Rule::exists('categories', 'id')],
            'website'     => ['nullable', 'url', 'max:255'],
            'image'       => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            'is_active'   => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'bg_color'    => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'parent_id'   => [
                'nullable', 
                'string', 
                Rule::exists('brands', 'id')->whereNull('parent_id'),
                function ($attribute, $value, $fail) use ($brandId) {
                    if ($value === $brandId) {
                        $fail('Operación inválida: Una marca no puede ser sub-marca de sí misma.');
                    }
                    // RECTIFICACIÓN: Espacio de nombres corregido apuntando quirúrgicamente al subdominio del catálogo
                    if ($value && Brand::where('parent_id', $brandId)->exists()) {
                        $fail('Restricción de nivel: Esta marca posee sub-marcas asignadas y no puede descender de nivel.');
                    }
                }
            ],
        ];
    }
}