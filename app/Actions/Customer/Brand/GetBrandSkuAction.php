<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetBrandSkuAction
{
    public function __construct(private readonly PriceResolverService $priceResolver) {}

    // --- DENTRO DE GetBrandSkuAction.php ---

    public function execute(string $brandId, string $branchId, array $filters = []): Collection
    {
        $query = Sku::query()
            ->select('skus.*', 
                DB::raw('COALESCE(inventory_balances.total_physical, 0) as total_physical'),
                DB::raw('COALESCE(inventory_balances.total_reserved, 0) as total_reserved'),
                DB::raw('COALESCE(inventory_balances.total_safety, 0) as total_safety')
            )
            ->where('skus.is_active', true) // CRÍTICO: Declaración explícita de tabla
            ->whereHas('product', fn($q) => $q->where('brand_id', $brandId))
            ->leftJoin('inventory_balances', function ($join) use ($branchId) {
                $join->on('skus.id', '=', 'inventory_balances.sku_id')
                    ->where('inventory_balances.branch_id', '=', $branchId);
            });

        // FILTRO SEGURO: Aislamiento lógico obligatorio (Previene fuga de marcas)
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('skus.name', 'like', "%{$term}%")
                  ->orWhere('skus.code', 'like', "%{$term}%");
            });
        }

        // MOTOR DE ORDENAMIENTO
        if (!empty($filters['sort']) && in_array($filters['sort'], ['price_asc', 'price_desc'])) {
            $direction = $filters['sort'] === 'price_asc' ? 'asc' : 'desc';
            $query->join('prices', 'skus.id', '=', 'prices.sku_id')
                  ->where('prices.branch_id', $branchId)
                  ->orderBy('prices.final_price', $direction);
        } else {
            $query->orderBy('skus.sort_order', 'asc');
        }

        // CARGA Y RESOLUCIÓN
        $skus = $query->with(['product.brand', 'prices' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }])->get();

        return $skus->map(function ($sku) {
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, 1, now());
            
            $sku->is_favorite = auth('customer')->check() 
                ? $sku->product->favoritedBy->contains(auth('customer')->id()) 
                : false;

            return $sku;
        });
    }
}