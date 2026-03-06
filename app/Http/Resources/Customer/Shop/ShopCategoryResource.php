<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Importación obligatoria

class ShopCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id, 
            'name'      => $this->name,
            'slug'      => $this->slug,
            // Aplicación del fallback en la Categoría
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path)
                : asset('assets/img/placeholder.png'),
            'type'      => 'category'
        ];
    }
}