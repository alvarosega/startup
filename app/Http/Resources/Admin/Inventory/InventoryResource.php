<?php

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalQty   = (int) $this->total_quantity;
        $safetyQty  = (int) $this->safety_quantity;
        $normalQty  = (int) $this->normal_quantity;
        $reservedQty= (int) $this->total_reserved;
        
        // LA LEY: Disponible real para la venta excluye el safety_stock
        $availableQty = $normalQty - $reservedQty;
        
        $minCost = (float) $this->min_cost;
        $maxCost = (float) $this->max_cost;
        $costDisplay = ($minCost === $maxCost) 
            ? number_format($minCost, 2) 
            : number_format($minCost, 2) . ' - ' . number_format($maxCost, 2);

        return [
            'sku_id'             => $this->sku_id,
            'branch_id'          => $this->branch_id,
            'sku_name'           => $this->sanitizeUTF8($this->sku?->name ?? 'S/N'),
            'sku_code'           => $this->sku?->code ?? 'S/N',
            'product_name'       => $this->sanitizeUTF8($this->sku?->product?->name ?? 'Desconocido'),
            'brand_name'         => $this->sanitizeUTF8($this->sku?->product?->brand?->name ?? 'Sin Marca'),
            'branch_name'        => $this->branch?->name ?? 'N/A',
            
            // Métricas desglosadas
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
        if ($available < 10) return 'LOW_STOCK';
        return 'IN_STOCK';
    }

    protected function sanitizeUTF8(?string $text): ?string {
        if (!$text) return null;
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
}