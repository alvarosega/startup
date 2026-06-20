<?php

declare(strict_types=1);

namespace App\Actions\Customer\Featured;

use App\Models\Product;
use App\DTOs\Customer\Featured\FeaturedProductDTO;
use App\Services\Inventory\InventoryLookupService;
use Illuminate\Support\Facades\Cache;

final readonly class GetHomeFeaturedAction
{
    public function __construct(
        private InventoryLookupService $inventoryService
    ) {}

    public function execute(string $branchId): array
    {
        $version = cache()->get('admin_products_version', 1);

        // Llave de caché única por sucursal para evitar fugas de inventario entre zonas
        return Cache::remember("featured_home_br_{$branchId}_v{$version}", 3600, function () use ($branchId) {
            return Product::query()
                ->select(['id', 'name', 'slug', 'image_path', 'brand_id'])
                ->with(['brand:id,name'])
                ->where('is_active', true)
                ->where('is_featured', true) // Filtro obligatorio
                // LEY DE EXISTENCIA: Solo productos con al menos un SKU con precio en esta sucursal
                ->whereHas('skus.prices', fn($q) => $q->where('branch_id', $branchId))
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(fn(Product $product) => new FeaturedProductDTO(
                    id: (string) $product->id, // <--- RECTIFICACIÓN: Parámetro obligatorio añadido
                    name: $product->name,
                    slug: $product->slug,
                    image_path: $product->image_path,
                    brand_name: $product->brand->name,
                    is_out_of_stock: $this->checkGlobalProductStock($product->id, $branchId)
                ))
                ->toArray();
        });
    }

    private function checkGlobalProductStock(string $productId, string $branchId): bool
    {
        return !\App\Models\Sku::query()
            ->join('inventory_balances as ib', 'skus.id', '=', 'ib.sku_id')
            ->where('skus.product_id', $productId)
            ->where('ib.branch_id', $branchId)
            // LEY DE DISPONIBILIDAD: Stock físico menos reservas
            ->whereRaw('(ib.total_physical - ib.total_reserved) > 0')
            ->exists(); // O(1) - Se detiene al encontrar el primer SKU con stock
    }
}