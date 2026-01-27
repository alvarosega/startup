<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            
            // Relación con Sucursal (Para el Index)
            'branch' => $this->whenLoaded('branch', fn() => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ]),

            'name' => $this->name,
            'description' => $this->description,
            'fixed_price' => $this->fixed_price,
            'is_active' => (bool) $this->is_active,
            'image_path' => $this->image_path,
            'updated_at' => $this->updated_at->diffForHumans(), // O format('Y-m-d')
            
            // Conteo (Para badges en Index)
            'skus_count' => $this->skus_count ?? 0, 

            // CASO 1: Para el Formulario de Edición (Logic)
            // Edit.vue espera: items: [{sku_id: 1, quantity: 2}]
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'sku_id' => $sku->id,
                    'quantity' => $sku->pivot->quantity
                ]);
            }),

            // CASO 2: Para la Vista Index (Display)
            // Index.vue espera: skus: [{name: 'Coca Cola', pivot: {quantity: 2}}]
            'skus' => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'id' => $sku->id,
                    'name' => $sku->name,
                    'code' => $sku->code,
                    'pivot' => [
                        'quantity' => $sku->pivot->quantity
                    ]
                ]);
            }),
            
            // Extras para valoraciones si las usas
            'reviews_avg_rating' => $this->reviews_avg_rating,
            'reviews_count' => $this->reviews_count,
        ];
    }
}