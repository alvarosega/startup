<?php
namespace App\Http\Resources\Admin\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            // Casteo forzado final por seguridad de la API
            'base_price' => (float) $this->base_price, 
            'prices_matrix' => $this->prices->groupBy('branch_id'),
        ];
    }
}