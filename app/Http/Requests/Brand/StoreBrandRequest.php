<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'slug' => ['nullable', 'string', 'max:255'],
            'provider_id' => ['nullable', 'exists:providers,id'], // UUID check automático
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'origin_country_code' => ['nullable', 'string', 'size:2'],
            'tier' => ['required', 'in:Economy,Standard,Premium,Luxury'],
            'website' => ['nullable', 'url'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'], // Max 2MB
            'categories' => ['array'],
            'categories.*' => ['exists:categories,id'],
        ];
    }
}