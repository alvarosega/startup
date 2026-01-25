<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SkuResource extends JsonResource
{
    public function toArray($request): array
    {
        // DEBUG
        \Log::info('SkuResource data:', [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'price' => $this->whenLoaded('prices', function() {
                return $this->prices->first() ? (float) $this->prices->first()->final_price : 0;
            }, 0),
            'conversion_factor' => (float) $this->conversion_factor,
            'weight' => (float) $this->weight,
            'image_path' => $this->image_path,
        ]);
        // La relación 'prices' ya viene filtrada desde el controller (solo nacionales)
        // Tomamos el primero (el más reciente gracias a latest())
        $currentPrice = $this->relationLoaded('prices') ? $this->prices->first() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'conversion_factor' => (float) $this->conversion_factor,
            'weight' => (float) $this->weight,
            'price' => $currentPrice ? (float) $currentPrice->final_price : 0,
            'image_path' => $this->image_path, // Asegúrate de incluir esto
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
        ];
    }
}