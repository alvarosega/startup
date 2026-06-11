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
        if ($this->name && !$this->slug) {
            $this->merge(['slug' => Str::slug($this->name)]);
        } elseif ($this->slug) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:100'],
            'slug'               => ['required', 'string', 'max:120', Rule::unique('categories')->where('deleted_epoch', 0)],
            'parent_id'          => ['nullable', 'uuid', Rule::exists('categories', 'id')->whereNull('parent_id')->withoutTrashed()],
            'external_code'      => ['nullable', 'string', 'max:50', Rule::unique('categories')->where('deleted_epoch', 0)],
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
        ];
    }
}