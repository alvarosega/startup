<?php

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalQty = (int) $this->total_quantity;
        $reservedQty = (int) $this->total_reserved;
        
        // Formateo del costo para respetar la regla de "No Promediar"
        $minCost = (float) $this->min_cost;
        $maxCost = (float) $this->max_cost;
        $costDisplay = ($minCost === $maxCost) 
            ? number_format($minCost, 2) 
            : number_format($minCost, 2) . ' - ' . number_format($maxCost, 2);

        return [
            // Identificadores de Agrupación
            'sku_id'             => $this->sku_id,
            'branch_id'          => $this->branch_id,
            
            // Datos Planos
            'sku_name'           => $this->sku?->name ?? 'S/N',
            'sku_code'           => $this->sku?->code ?? 'S/N',
            'product_name'       => $this->sku?->product?->name ?? 'Desconocido',
            'brand_name'         => $this->sku?->product?->brand?->name ?? 'Sin Marca',
            'branch_name'        => $this->branch?->name ?? 'N/A',
            
            // Matemáticas de Stock
            'total_quantity'     => $totalQty,
            'total_reserved'     => $reservedQty,
            'available_quantity' => $totalQty - $reservedQty, // Stock real para la venta
            
            // Valoración
            'cost_range'         => $costDisplay,
            
            // Indicador de Riesgo
            'status'             => $this->calculateStockStatus($totalQty - $reservedQty)
        ];
    }

    private function calculateStockStatus(int $available): string
    {
        if ($available <= 0) return 'OUT_OF_STOCK';
        if ($available < 10) return 'LOW_STOCK'; // Umbral estático por ahora
        return 'IN_STOCK';
    }
}