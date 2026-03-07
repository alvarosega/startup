<?php

namespace App\Http\Requests\Admin\Brand; // CORREGIR NAMESPACE

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $brandId = $this->route('brand')->id;
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands')->ignore($brandId)->whereNull('deleted_at')],
            'provider_id'    => ['required', 'uuid', 'exists:providers,id'],
            'category_id'    => ['required', 'uuid', 'exists:categories,id'],
            'market_zone_id' => ['required', 'uuid', 'exists:market_zones,id'], // AÑADIR ESTE
            'website'        => ['nullable', 'url'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'is_active'      => ['boolean'],
            'is_featured'    => ['boolean'],
            'sort_order'     => ['integer', 'min:0'],
            'description'    => ['nullable', 'string'],
        ];
    }
}