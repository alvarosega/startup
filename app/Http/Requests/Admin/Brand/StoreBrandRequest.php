<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255', 
                // MODIFICAR: Añadir whereNull para SoftDeletes
                \Illuminate\Validation\Rule::unique('brands')->whereNull('deleted_at')
            ],
            'provider_id'    => ['required', 'exists:providers,id'],
            'category_id'    => ['required', 'exists:categories,id'],
            'market_zone_id' => ['required', 'exists:market_zones,id'], 
            'website'        => ['nullable', 'url'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'is_active'      => ['boolean'],
            'is_featured'    => ['boolean'],
            'sort_order'     => ['integer', 'min:0'],
            'description'    => ['nullable', 'string'],
        ];
    }
}