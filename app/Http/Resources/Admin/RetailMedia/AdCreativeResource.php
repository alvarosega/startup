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
            'name'              => mb_convert_encoding((string) $this->name, 'UTF-8'),
            'image_mobile_url'  => $this->image_mobile_path ? Storage::disk('public')->url($this->image_mobile_path) : null,
            'image_desktop_url' => $this->image_desktop_path ? Storage::disk('public')->url($this->image_desktop_path) : null,
            'is_active'         => (bool) $this->is_active,
            'sort_order'        => (int) $this->sort_order,
            'action_type'       => (string) $this->action_type,
            
            // ACLARACIÓN: Requerido por Index.vue para renderizado de etiquetas
            'placement' => [
                'id'   => (string) ($this->placement?->id ?? ''),
                'name' => (string) ($this->placement?->name ?? 'Sin Ubicación'),
                'code' => (string) ($this->placement?->code ?? 'NONE'),
            ],

            'campaign' => [
                'id'   => (string) ($this->campaign?->id ?? ''),
                'name' => (string) ($this->campaign?->name ?? 'Campaña Interna'),
            ],

            'brand' => $this->brand ? [
                'id'   => (string) $this->brand->id,
                'name' => (string) $this->brand->name,
            ] : null,

            'branch' => [
                'id'   => (string) ($this->branch?->id ?? ''),
                'name' => (string) ($this->branch?->name ?? 'Global'),
            ],

            'category' => $this->category ? [
                'id'   => (string) $this->category->id,
                'name' => (string) $this->category->name,
            ] : null,

            'target' => [
                'id'   => (string) $this->target_id,
                'type' => $this->resolveTargetType(),
                'name' => $this->resolveTargetName(),
                'bundle_subtype' => $this->bundle?->type ?? null,
            ],
        ];
    }

    private function resolveTargetType(): string
    {
        return strtolower(class_basename($this->target_type));
    }

    private function resolveTargetName(): string
    {
        $type = $this->resolveTargetType();

        return match($type) {
            'sku'    => ($this->sku?->product?->name ?? 'SKU') . " - " . ($this->sku?->name ?? ''),
            'bundle' => (string) ($this->bundle?->name ?? 'Pack'),
            'brand'  => (string) ($this->brand?->name ?? 'Marca'),
            default  => 'Destino no definido',
        };
    }
}