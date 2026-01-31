<?php

namespace App\Http\Resources\Shop;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShopProductResource extends JsonResource
{
    protected ?int $contextBranchId = null;

    public function setContextBranch(int $branchId): self
    {
        $this->contextBranchId = $branchId;
        return $this;
    }

    public function toArray($request): array
    {
        $targetBranchId = $this->contextBranchId;

        // Iteramos sobre los SKUs del producto
        $variants = $this->skus->map(function ($sku) use ($targetBranchId) {
            
            // --- 1. LÓGICA DE PRECIOS (JERARQUÍA) ---
            
            // A. Buscamos si existe un precio configurado en la tabla 'prices' para esta sucursal
            $branchPriceObj = $sku->prices->firstWhere('branch_id', $targetBranchId);
            
            if ($branchPriceObj) {
                // PRIORIDAD 1: Precio específico de sucursal (u oferta)
                $finalPrice = (float) $branchPriceObj->final_price;
                $listPrice  = (float) $branchPriceObj->list_price;
            } else {
                // PRIORIDAD 2 (FALLBACK): Precio Base del SKU
                // Si no hay registro en 'prices', leemos la columna 'base_price' de la tabla 'skus'
                $finalPrice = (float) $sku->base_price;
                $listPrice  = 0.00; 
            }

            // --- 2. LÓGICA DE STOCK ---
            // Sumamos el inventario de esta sucursal
            $stock = $sku->inventoryLots
                ->where('branch_id', $targetBranchId)
                ->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);

            return [
                'id' => $sku->id,
                'name' => $sku->name, // Ej: "Lata 350ml"
                'code' => $sku->code,
                'price' => $finalPrice,
                'list_price' => $listPrice > $finalPrice ? $listPrice : null,
                'stock' => (int)$stock,
                'has_stock' => $stock > 0,
            ];
        });

        // --- FILTRO DE SEGURIDAD ---
        // Solo mostramos variantes que tengan un precio mayor a 0.
        // SI TUS PRODUCTOS TIENEN base_price = 0 EN LA BD, DESAPARECERÁN AQUÍ.
        $activeVariants = $variants->filter(fn($v) => $v['price'] > 0);

        // Cálculos para la tarjeta principal (Header del producto)
        if ($activeVariants->isEmpty()) {
            $minPrice = 0; $maxPrice = 0; $totalStock = 0;
        } else {
            $minPrice = $activeVariants->min('price');
            $maxPrice = $activeVariants->max('price');
            $totalStock = $activeVariants->sum('stock');
        }

        // Formato visual del precio ("Bs 10.00")
        if ($minPrice == 0) {
            $priceDisplay = 'Consultar';
        } elseif ($minPrice === $maxPrice) {
            $priceDisplay = number_format($minPrice, 2);
        } else {
            $priceDisplay = number_format($minPrice, 2) . " - " . number_format($maxPrice, 2);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand ? $this->brand->name : null,
            'image_url' => $this->image_url,
            
            // Datos para la vista
            'price_display' => $priceDisplay,
            'has_stock' => $totalStock > 0,
            'stock_quantity' => (int) $totalStock,
            
            // --- ESTO ES LO QUE LLENA EL MODAL ---
            // Enviamos .values() para re-indexar el array y que JSON no lo convierta en objeto
            'variants' => $activeVariants->values(),
        ];
    }
}