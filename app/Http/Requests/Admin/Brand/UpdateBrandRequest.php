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
        if ($this->name) {
            $this->merge([
                'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->name),
                'parent_id' => $this->parent_id ?: null,
            ]);
        }
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id;

        return [
            'name'              => ['required', 'string', 'max:100'],
            'slug'              => ['required', 'string', 'max:120', Rule::unique('brands')->ignore($brandId)->withoutTrashed()],
            'parent_id'         => ['nullable', 'uuid', Rule::exists('brands', 'id')->whereNot('id', $brandId)->withoutTrashed()],
            'provider_id'       => ['required', 'uuid', Rule::exists('providers', 'id')],
            'category_id'       => ['required', 'uuid', Rule::exists('categories', 'id')->withoutTrashed()],
            
            // REPARACIÓN CRÍTICA: Validar array de zonas
            'market_zone_ids'   => ['required', 'array', 'min:1'],
            'market_zone_ids.*' => ['uuid', Rule::exists('market_zones', 'id')->withoutTrashed()],
            
            'website'           => ['nullable', 'url', 'max:255'],
            'image'             => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            'is_active'         => ['boolean'],
            'is_featured'       => ['boolean'],
            'sort_order'        => ['integer', 'min:0'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ];
    }
}