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
            ->whereHas('product', fn($q) => $q->where('brand_id', $brandId))
            ->active()
            // 1. OPTIMIZACIÓN: Cambiamos subqueries por Join a Balances (Snapshot)
            ->leftJoin('inventory_balances', function ($join) use ($branchId) {
                $join->on('skus.id', '=', 'inventory_balances.sku_id')
                    ->where('inventory_balances.branch_id', '=', $branchId);
            })
            ->select('skus.*', 
                DB::raw('COALESCE(inventory_balances.total_physical, 0) as total_physical'),
                DB::raw('COALESCE(inventory_balances.total_reserved, 0) as total_reserved'),
                DB::raw('COALESCE(inventory_balances.total_safety, 0) as total_safety')
            );

        // ... (Mantener lógica de filtros search/sort) ...

        // 2. CARGA DE PRECIOS: Solo los de la sucursal actual
        $skus = $query->with(['product.brand', 'prices' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }])->get();

        return $skus->map(function ($sku) {
            // Inyectamos el precio ganador (Bs) para cantidad 1
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, 1, now());
            
            // Atributo dinámico para el SkuResource
            $sku->is_favorite = auth('customer')->check() 
                ? $sku->product->favoritedBy->contains(auth('customer')->id()) 
                : false;

            return $sku;
        });
    }
}