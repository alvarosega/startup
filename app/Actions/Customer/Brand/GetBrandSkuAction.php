<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetBrandSkuAction
{
    public function __construct(private readonly PriceResolverService $priceResolver) {}

    public function execute(string $brandId, string $branchId, array $filters = []): Collection
    {
        $query = Sku::query()
            ->whereHas('product', fn($q) => $q->where('brand_id', $brandId))
            ->active()
            ->addSelect([
                'total_physical' => DB::table('inventory_lots')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $branchId),
                'total_reserved' => DB::table('inventory_lots')
                    ->selectRaw('COALESCE(SUM(reserved_quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $branchId),
            ]);

        // Manejo seguro de búsqueda
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn($q) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            );
        }

        // CORRECCIÓN CRÍTICA: Manejo seguro de 'sort'
        $sortMethod = $filters['sort'] ?? 'relevance';

        $query = match($sortMethod) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            default      => $query->orderBy('sort_order', 'asc'),
        };

        $skus = $query->with(['product.category', 'product.brand', 'prices' => fn($q) => $q->where('branch_id', $branchId)])
            ->get();

        return $skus->map(function ($sku) use ($branchId) {
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, $branchId, 1);
            $sku->bg_color = $sku->product->category->bg_color ?? 'ef4444';
            $sku->brand_name = $sku->product->brand->name ?? '';
            return $sku;
        })->filter(fn($sku) => ($sku->total_physical - $sku->total_reserved) > 0);
    }
}