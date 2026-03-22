<?php

namespace App\Http\Requests\Admin\RetailMedia;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAdCreativeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $isUpdate = $this->filled('id');

        return [
            'id' => ['nullable', 'uuid'],
            'campaign_id' => ['required', 'uuid', 'exists:ad_campaigns,id'],
            'placement_id' => ['required', 'uuid', 'exists:ad_placements,id'],
            'sku_id' => ['required', 'uuid', 'exists:skus,id'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['uuid', 'exists:branches,id'],
            
            // Imágenes: Requeridas en creación, opcionales en update
            'image_mobile' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:webp,jpg,png', 'max:2048'],
            'image_desktop' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:webp,jpg,png', 'max:4096'],
        ];
    }
}