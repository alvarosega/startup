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
            'id'                => (string) $this->id,
            'name'              => (string) $this->name,
            'image_mobile_url'  => $this->image_mobile_path ? Storage::disk('public')->url($this->image_mobile_path) : null,
            'image_desktop_url' => $this->image_desktop_path ? Storage::disk('public')->url($this->image_desktop_path) : null,
            'is_active'         => (bool) $this->is_active,
            'sort_order'        => (int) $this->sort_order,
            'action_type'       => (string) $this->action_type,
            
            'campaign' => [
                'id'       => $this->campaign->id,
                'name'     => $this->campaign->name,
                'provider' => $this->campaign->provider->commercial_name ?? 'Interno',
            ],

            'placement' => [
                'id'   => $this->placement->id,
                'name' => $this->placement->name,
                'code' => strtoupper($this->placement->code), 
            ],

            'category' => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,

            'branch' => [
                'id'   => $this->branch->id,
                'name' => $this->branch->name,
            ],

            'target' => [
                'id'             => (string) $this->target_id,
                'type'           => strtolower(class_basename($this->target_type)), // Convierte "App\Models\Sku" a "sku"
                'name'           => $this->resolveTargetName(),
                'bundle_subtype' => ($this->target_type === 'bundle' || str_contains($this->target_type, 'Bundle')) && $this->bundle
                                    ? $this->bundle->type 
                                    : null,
            ],
        ];
    }

    private function resolveTargetName(): string
    {
        // Limpiamos el target_type (ej. de "App\Models\Sku" a "sku")
        $type = strtolower(class_basename($this->target_type));

        if ($type === 'sku' && $this->sku) {
            return ($this->sku->product->name ?? 'Producto') . " - " . $this->sku->name;
        }
    
        if ($type === 'bundle' && $this->bundle) {
            return (string) $this->bundle->name;
        }
    
        return 'Destino no encontrado o ID inválido';
    }
}