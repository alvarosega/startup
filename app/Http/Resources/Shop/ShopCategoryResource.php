<?php

namespace App\Http\Resources\Shop;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShopCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Aquí forzamos la generación de la URL pública.
            // Si image_path existe en BD, generamos la URL. Si no, enviamos null.
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            
            // Opcional: para debug o lógica futura
            'has_image' => !empty($this->image_path),
        ];
    }
}