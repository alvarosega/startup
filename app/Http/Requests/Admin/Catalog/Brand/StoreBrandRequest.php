<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Catalog\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->name ?? ''),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:120', Rule::unique('brands')->where('deleted_epoch', 0)],
            'parent_id'   => ['nullable', 'uuid', Rule::exists('brands', 'id')->whereNull('parent_id')->withoutTrashed()],
            'provider_id' => ['required', 'uuid', Rule::exists('providers', 'id')],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')->withoutTrashed()],
            'website'     => ['nullable', 'url', 'max:255'],
            'image'       => ['nullable', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            'is_active'   => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'bg_color'    => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ];
    }
}