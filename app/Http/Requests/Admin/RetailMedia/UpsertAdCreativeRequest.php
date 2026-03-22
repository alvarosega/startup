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
            'branch_id' => ['required', 'uuid', 'exists:branches,id'], // Cambiado a singular
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'], // Nuevo y opcional
            'target_id' => ['required', 'uuid'],
            'target_type' => ['required', 'string', 'in:sku,bundle'],
            'name' => ['required', 'string', 'max:255'],
            'action_type' => ['required', 'string', 'in:ADD_TO_CART,NAVIGATE'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'image_mobile' => [$isUpdate ? 'nullable' : 'required', 'image', 'max:2048'],
            'image_desktop' => [$isUpdate ? 'nullable' : 'required', 'image', 'max:4096'],
        ];
    }
}