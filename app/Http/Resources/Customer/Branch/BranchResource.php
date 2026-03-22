<?php

namespace App\Http\Resources\Customer\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transforma la sucursal para la vista pública/cliente.
     * Sanitización estricta: Solo coordenadas y polígonos de cobertura.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'name'      => (string) $this->name,
            'latitude'  => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'polygon'   => $this->coverage_polygon, // Necesario para dibujar en el mapa de registro
        ];
    }
}