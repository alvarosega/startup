<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdCreativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => (string) $this->id,
            'campaign_id'        => (string) $this->campaign_id,
            'campaign_name'      => $this->relationLoaded('campaign') ? mb_toUpperCase((string) $this->campaign->name) : null,
            'placement_id'       => (string) $this->placement_id,
            'placement_name'     => $this->relationLoaded('placement') ? mb_toUpperCase((string) $this->placement->name) : null,
            'branch_id'          => (string) $this->branch_id,
            'branch_name'        => $this->relationLoaded('branch') ? mb_toUpperCase((string) $this->branch->name) : null,
            
            // Relaciones de Redirección Plana
            'sku_id'             => (string) $this->sku_id,
            'sku_name'           => $this->relationLoaded('sku') ? mb_toUpperCase((string) $this->sku->name) : null,
            'category_id'        => (string) $this->category_id,
            'category_name'      => $this->relationLoaded('category') ? mb_toUpperCase((string) $this->category->name) : null,
            'brand_id'           => (string) $this->brand_id,
            'brand_name'         => $this->relationLoaded('brand') ? mb_toUpperCase((string) $this->brand->name) : null,
            'bundle_id'          => (string) $this->bundle_id,
            'bundle_name'        => $this->relationLoaded('bundle') ? mb_toUpperCase((string) $this->bundle->name) : null,
            
            'name'               => mb_toUpperCase((string) $this->name),
            'image_mobile_url'   => $this->image_mobile_path ? Storage::url($this->image_mobile_path) : null,
            'image_desktop_url'  => $this->image_desktop_path ? Storage::url($this->image_desktop_path) : null,
            'action_type'        => (string) $this->action_type,
            'sort_order'         => (int) $this->sort_order,
            'is_active'          => (bool) $this->is_active,
        ];
    }
}