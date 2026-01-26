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

        // Mapeamos los SKUs (Variantes)
        $variants = $this->skus->map(function ($sku) use ($targetBranchId) {
            
            // 1. PRECIO: Buscamos el precio específico de esta sucursal
            $priceObj = $sku->prices->firstWhere('branch_id', $targetBranchId);
            
            // Si no hay precio para esta sucursal, intentamos el global (null)
            // PERO: Si tu negocio es "Exclusivo", quizás no deberías hacer fallback.
            // Lo dejamos con fallback por seguridad, pero el precio vendrá del Seeder.
            if (!$priceObj) {
                $priceObj = $sku->prices->firstWhere('branch_id', null);
            }

            $price = $priceObj ? (float)$priceObj->final_price : 0.00;
            $listPrice = $priceObj ? (float)$priceObj->list_price : 0.00;

            // 2. STOCK: Sumamos solo los lotes de esta sucursal
            // La relación 'inventoryLots' ya debería venir filtrada por el Action,
            // pero filtramos aquí de nuevo en memoria para asegurar.
            $stock = $sku->inventoryLots
                ->where('branch_id', $targetBranchId)
                ->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);

            return [
                'id' => $sku->id,
                'name' => $sku->name, // Ej: "Botella 620ml"
                'code' => $sku->code,
                'price' => $price,
                'list_price' => $listPrice > $price ? $listPrice : null, // Oferta
                'stock' => (int)$stock,
                'has_stock' => $stock > 0,
            ];
        });

        // Filtrar variantes que no tienen precio (no se venden en esta sucursal)
        // Esto es clave para la "Exclusividad". Si precio es 0, lo quitamos.
        $activeVariants = $variants->filter(fn($v) => $v['price'] > 0);

        // Si no queda ninguna variante activa, el producto está "No disponible"
        if ($activeVariants->isEmpty()) {
            $minPrice = 0;
            $maxPrice = 0;
            $totalStock = 0;
        } else {
            $minPrice = $activeVariants->min('price');
            $maxPrice = $activeVariants->max('price');
            $totalStock = $activeVariants->sum('stock');
        }

        // Formato de precio visual
        if ($minPrice === 0 && $maxPrice === 0) {
            $priceDisplay = 'No disponible';
        } elseif ($minPrice === $maxPrice) {
            $priceDisplay = number_format($minPrice, 2);
        } else {
            $priceDisplay = number_format($minPrice, 2) . " - " . number_format($maxPrice, 2);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand->name ?? '',
            'category' => $this->category->name ?? '',
            'image_url' => $this->image_path ? Storage::url($this->image_path) : '/images/placeholder.png',
            
            // Datos Consolidados
            'price_display' => $priceDisplay,
            'price_raw' => $minPrice, // Útil para ordenar en frontend
            'total_stock' => $totalStock,
            'has_stock' => $totalStock > 0,
            'is_available' => $activeVariants->isNotEmpty(), // Si vende al menos una variante
            'stock_source_id' => $this->contextBranchId,
            // Detalle
            'variants' => $activeVariants->values(),
        ];
    }
}