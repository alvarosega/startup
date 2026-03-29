<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAdCreativeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $isUpdate = $this->filled('id');

        return [
            'id'            => ['nullable', 'uuid'],
            'campaign_id'   => ['required', 'uuid', 'exists:ad_campaigns,id'],
            'placement_id'  => ['required', 'uuid', 'exists:ad_placements,id'],
            'branch_id'     => ['required', 'uuid', 'exists:branches,id'],
            'brand_id'      => ['nullable', 'uuid', 'exists:brands,id'],
            'category_id'   => ['nullable', 'uuid', 'exists:categories,id'],
            'target_id'     => ['required', 'uuid'],
            'target_type'   => ['required', 'string', 'in:sku,bundle,brand'],
            'name'          => ['required', 'string', 'max:100'],
            'action_type'   => ['required', 'string', 'in:ADD_TO_CART,NAVIGATE'],
            'sort_order'    => ['required', 'integer', 'min:0'],
            'is_active'     => ['required', 'boolean'],
            'image_mobile'  => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:webp,png', 'max:1024'],
            'image_desktop' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:webp,png', 'max:2048'],
        ];
    }
}