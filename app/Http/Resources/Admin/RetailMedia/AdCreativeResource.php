<?php

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdCreativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => mb_convert_encoding($this->name, 'UTF-8', 'UTF-8'),
            'image_mobile_url' => $this->image_mobile_path ? \Storage::disk('public')->url($this->image_mobile_path) : null,
            'image_desktop_url' => $this->image_desktop_path ? \Storage::disk('public')->url($this->image_desktop_path) : null,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'action_type' => $this->action_type,
            
            'campaign' => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ],
            'placement' => [
                'name' => $this->placement->name,
                'code' => $this->placement->code,
            ],
            // Contexto de Categoría (Nuevo)
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            // Anclaje Único
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ],
            // Target Polimórfico (SKU o Bundle)
            'target' => [
                'id'   => $this->target_id,
                'type' => strtolower(class_basename($this->target_type)),
                'name' => $this->target->name ?? 'N/A', // El modelo 'target' puede ser SKU o Bundle
            ],
        ];
    }
}