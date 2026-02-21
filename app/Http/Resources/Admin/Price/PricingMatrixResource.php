<?php
namespace App\Http\Resources\Admin\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_url,
            // Delegamos la transformación de los SKUs
            'skus' => PricingSkuResource::collection($this->whenLoaded('skus')),
        ];
    }
}