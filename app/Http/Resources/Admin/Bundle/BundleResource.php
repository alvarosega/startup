<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => mb_strtoupper((string) $this->name),
            'image_url'   => $this->image_path ? Storage::url($this->image_path) : null,
            'type'        => (string) $this->type,
            'starts_at'   => $this->starts_at?->format('Y-m-d H:i:s'),
            'ends_at'     => $this->ends_at?->format('Y-m-d H:i:s'),
            'is_active'   => (bool) $this->is_active,
            
            // Carga diferida condicionada bajo demanda explicita del controlador
            'items'       => BundleItemResource::collection($this->whenLoaded('items')),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}