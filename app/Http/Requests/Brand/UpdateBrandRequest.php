<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('brand')->id;

        return [
            'name'                => ['required', 'string', 'max:255', Rule::unique('brands')->ignore($id)],
            'provider_id'         => ['required', 'exists:providers,id'],
            'manufacturer'        => ['nullable', 'string', 'max:255'],
            'origin_country_code' => ['nullable', 'string', 'size:2'],
            'tier'                => ['required', 'in:Economy,Standard,Premium,Luxury'],
            'website'             => ['nullable', 'url', 'max:255'],
            
            'categories'          => ['nullable', 'array'],
            'categories.*'        => ['exists:categories,id'],
            
            'image'               => ['nullable', 'image', 'max:2048'],
            'is_active'           => ['boolean'],
            'is_featured'         => ['boolean'],
        ];
    }
}