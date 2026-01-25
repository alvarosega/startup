<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands')->ignore($brandId)],
            'slug' => ['nullable', 'string', 'max:255'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'origin_country_code' => ['nullable', 'string', 'size:2'],
            'tier' => ['required', 'in:Economy,Standard,Premium,Luxury'],
            'website' => ['nullable', 'url'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'categories' => ['array'],
            'categories.*' => ['exists:categories,id'],
        ];
    }
}