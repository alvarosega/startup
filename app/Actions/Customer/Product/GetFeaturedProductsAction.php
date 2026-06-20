<?php

namespace App\Actions\Customer\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\DTOs\Customer\Product\ProductSummaryDTO;

class GetFeaturedProductsAction
{
    public function execute(string $branchId): array
    {
        $version = cache()->get('admin_products_version', 1);

        return cache()->remember("featured_products_br_{$branchId}_v{$version}", 3600, function () use ($branchId) {
            return Product::query()
                ->select(['id', 'name', 'slug', 'image_path']) // Query Law: Solo lo necesario
                ->where('is_active', true)
                ->where('is_featured', true)
                // 1. Check de Existencia en Sucursal (Precios)
                ->whereHas('skus.prices', fn($q) => $q->where('branch_id', $branchId))
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(function ($product) use ($branchId) {
                    // 2. Lógica de "Agotado" (Cálculo de inventario real)
                    $totalStock = DB::table('inventory_lots')
                        ->join('skus', 'inventory_lots.sku_id', '=', 'skus.id')
                        ->where('skus.product_id', $product->id)
                        ->where('inventory_lots.branch_id', $branchId)
                        ->where('inventory_lots.is_safety_stock', false)
                        ->sum(DB::raw('quantity - reserved_quantity'));

                    return new ProductSummaryDTO(
                        name: $product->name,
                        slug: $product->slug,
                        image_path: $product->image_path,
                        is_out_of_stock: $totalStock <= 0
                    );
                })
                ->toArray();
        });
    }
}