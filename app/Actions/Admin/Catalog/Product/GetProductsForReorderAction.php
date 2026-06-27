<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;

class GetProductsForReorderAction
{
    /**
     * RECTIFICACIÓN: Extrae del controlador la consulta secuencial de ordenamiento general.
     */
    public function execute(): array
    {
        return Product::select(['id', 'name', 'image_path', 'sort_order'])
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($product) => [
                'id'         => (string) $product->id,
                'name'       => (string) $product->name,
                'image_path' => $product->image_path ? (string) $product->image_path : null,
                'sort_order' => (int) $product->sort_order,
            ])
            ->toArray();
    }
}