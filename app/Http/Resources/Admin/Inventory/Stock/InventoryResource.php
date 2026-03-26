<?php

namespace App\Http\Resources\Admin\Inventory\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // El objeto es un stdClass del Query Builder, no un Modelo Eloquent
        $totalQty    = (int) ($this->total_quantity ?? 0);
        $safetyQty   = (int) ($this->safety_quantity ?? 0);
        $reservedQty = (int) ($this->total_reserved ?? 0);
        
        // Lógica de Negocio: Stock Ordinario = Total - Seguridad
        $normalQty = max(0, $totalQty - $safetyQty);
        
        // Disponible Comercial = Stock Ordinario - Reservas
        $availableQty = max(0, $normalQty - $reservedQty);
        
        $minCost = (float) ($this->min_cost ?? 0);
        $maxCost = (float) ($this->max_cost ?? 0);
        
        $costDisplay = ($minCost === $maxCost) 
            ? number_format($minCost, 2) 
            : number_format($minCost, 2) . ' - ' . number_format($maxCost, 2);

        return [
            'sku_id'             => $this->sku_id,
            'branch_id'          => $this->branch_id,
            'sku_name'           => $this->sku_name ?? 'S/N',
            'sku_code'           => $this->sku_code ?? 'S/N',
            'product_name'       => $this->product_name ?? 'Desconocido',
            'brand_name'         => $this->brand_name ?? 'Sin Marca',
            'branch_name'        => $this->branch_name ?? 'N/A',
            
            'total_quantity'     => $totalQty,
            'normal_quantity'    => $normalQty,
            'safety_quantity'    => $safetyQty,
            'total_reserved'     => $reservedQty,
            'available_quantity' => $availableQty,
            
            'cost_range'         => $costDisplay,
            'status'             => $this->calculateStockStatus($availableQty)
        ];
    }

    private function calculateStockStatus(int $available): string
    {
        if ($available <= 0) return 'OUT_OF_STOCK';
        if ($available < 10) return 'LOW_STOCK'; // Umbral configurable
        return 'IN_STOCK';
    }
}