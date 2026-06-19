<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $typeMap = [
            'App\Models\Sku'      => 'sku',
            'App\Models\Category' => 'category',
            'App\Models\Bundle'   => 'bundle',
        ];

        return [
            'id'                 => $this->id,
            'campaign_id'        => $this->campaign_id,
            'placement_code'     => $this->placement?->code,
            'branch_name'        => $this->branch?->name,
            'name'               => $this->name,
            'image_mobile'       => $this->image_mobile_path ? asset('storage/' . $this->image_mobile_path) : null,
            'image_desktop'      => $this->image_desktop_path ? asset('storage/' . $this->image_desktop_path) : null,
            'action_type'        => $this->action_type,
            'sort_order'         => $this->sort_order,
            'is_active'          => $this->is_active,
            'target_type'        => $typeMap[$this->target_type] ?? 'unknown',
            'target_id'          => $this->target_id,
            'target_display'     => $this->target?->name ?? $this->target?->code ?? 'N/D',
            'anchor'             => $this->sku?->name ?? $this->category?->name ?? $this->bundle?->name ?? $this->brand?->name ?? 'Global'
        ];
    }
}