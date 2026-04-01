<?php

namespace App\Actions\Customer\Category;

use App\Models\{Sku, Category, AdCreative};
use App\DTOs\Customer\Shop\Category\CategoryPageDTO;
use Illuminate\Support\Facades\{Cache, DB};

final class GetCategoryPageAction
{
    // --- REEMPLAZAR EL MÉTODO COMPLETO POR ESTE ---
    public function execute(CategoryPageDTO $dto, ?string $searchTerm = null): array
    {
        $allCategories = Cache::remember("global_nav_br_{$dto->branchId}", 3600, function () use ($dto) {
            return Category::active()
                ->whereHas('products.skus.prices', fn($q) => $q->where('branch_id', $dto->branchId))
                ->orderBy('sort_order')
                ->get();
        });
    

        // 2. Banners de Categoría
        $banners = AdCreative::query()
            ->where('branch_id', $dto->branchId)
            ->where('category_id', $dto->categoryId)
            ->where('is_active', true)
            ->with(['target'])
            ->orderBy('sort_order')
            ->get();

        // 3. SKUs con Redis y Búsqueda
        // CORRECCIÓN: Usar $searchTerm (la variable que entra por parámetro)
        $searchHash = $searchTerm ? md5(strtolower(trim($searchTerm))) : 'all';
        $cacheKey = "cat_{$dto->categoryId}_br_{$dto->branchId}_s_{$searchHash}";

        $products = Cache::remember($cacheKey, 600, function () use ($dto, $searchTerm) {
            $query = Sku::query()
                ->whereHas('product', fn($q) => $q->where('category_id', $dto->categoryId))
                ->where('is_active', true);

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('code', 'like', "%{$searchTerm}%")
                      ->orWhereHas('product.brand', fn($bq) => $bq->where('name', 'like', "%{$searchTerm}%"));
                });
            }

            // Orden estratégico ASC para coincidir con el Admin (10, 20, 30...)
            return $query->orderBy('sort_order', 'asc')
                ->addSelect([
                    'available_stock' => DB::table('inventory_lots')
                        ->selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                        ->whereColumn('sku_id', 'skus.id')
                        ->where('branch_id', $dto->branchId)
                        ->where('is_safety_stock', false)
                ])
                ->with(['product.brand', 'prices' => fn($q) => $q->where('branch_id', $dto->branchId)])
                ->get();
        });

        return [
            'all_categories' => $allCategories,
            'banners'        => $banners,
            'products'       => $products
        ];
    }
}