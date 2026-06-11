<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->name),
        ]);
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id;

        return [
            'name'            => ['required', 'string', 'max:100'],
            'slug'            => ['required', 'string', 'max:120', Rule::unique('brands')->ignore($brandId)->where('deleted_epoch', 0)],
            'parent_id'       => [
                'nullable', 
                'uuid', 
                Rule::exists('brands', 'id')->whereNull('parent_id')->withoutTrashed(),
                function ($attribute, $value, $fail) use ($brandId) {
                    if ($value === $brandId) {
                        $fail('Operación inválida: Una marca no puede ser sub-marca de sí misma.');
                    }
                    if ($value && \App\Models\Brand::where('parent_id', $brandId)->exists()) {
                        $fail('Restricción de nivel: Esta marca posee sub-marcas asignadas y no puede descender de nivel.');
                    }
                }
            ],
            'provider_id'     => ['required', 'uuid', Rule::exists('providers', 'id')],
            'category_id'     => ['required', 'uuid', Rule::exists('categories', 'id')->whereNull('parent_id')->withoutTrashed()],
            'website'         => ['nullable', 'url', 'max:255'],
            'image'           => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            'is_active'       => ['required', 'boolean'],
            'is_featured'     => ['required', 'boolean'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'bg_color'        => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ];
    }
}