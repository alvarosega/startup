<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255', 'unique:brands,name'],
            'provider_id'         => ['required', 'exists:providers,id'],
            'manufacturer'        => ['nullable', 'string', 'max:255'],
            'origin_country_code' => ['nullable', 'string', 'size:2'], // ISO 2 chars (BO, US)
            'tier'                => ['required', 'in:Economy,Standard,Premium,Luxury'],
            'website'             => ['nullable', 'url', 'max:255'],
            
            'categories'          => ['nullable', 'array'],
            'categories.*'        => ['exists:categories,id'], // Cada ID debe existir
            
            'image'               => ['nullable', 'image', 'max:2048'],
            'is_active'           => ['boolean'],
            'is_featured'         => ['boolean'],
        ];
    }
}