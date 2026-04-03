<?php

declare(strict_types=1);

namespace App\Actions\Customer\Sku;

use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use App\Actions\Customer\Cart\GetCustomerCartAction;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListSkusAction
{
    public function __construct(
        private PriceResolverService $priceResolver,
        private GetCustomerCartAction $getCartAction
    ) {}

    public function execute(string $categoryId, string $branchId, ?string $customerId, array $filters): CursorPaginator
    {
        $now = now();
        $nowStr = $now->toDateTimeString();
    
        // 1. RECTIFICACIÓN DE CONTRATO: Pasar los 3 argumentos obligatorios
        $guestUuid = session('guest_client_uuid') ?? request()->header('X-Guest-UUID');
        $cart = $this->getCartAction->execute($guestUuid, $customerId, $branchId);
        $cartQuantities = collect($cart['items'] ?? [])->pluck('quantity', 'sku_id');

        $query = Sku::query()
            // LEY DE ALTA DENSIDAD: Eager loading filtrado para evitar N+1
            ->with(['product.brand', 'prices' => function ($q) use ($branchId, $nowStr) {
                $q->where('branch_id', $branchId)
                  ->where('valid_from', '<=', $nowStr)
                  ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $nowStr));
            }])
            ->join('products as p', 'skus.product_id', '=', 'p.id')
            ->leftJoin('inventory_balances as ib', function ($join) use ($branchId) {
                $join->on('skus.id', '=', 'ib.sku_id')
                    ->where('ib.branch_id', '=', $branchId);
            })
            ->select([
                'skus.*',
                'p.name as product_name',
                // Columnas necesarias para el Accessor 'available_stock'
                DB::raw('COALESCE(ib.total_physical, 0) as total_physical'),
                DB::raw('COALESCE(ib.total_reserved, 0) as total_reserved'),
                // Subconsulta optimizada para ordenamiento (Price Law)
                DB::raw("(
                    SELECT final_price FROM prices 
                    WHERE prices.sku_id = skus.id 
                    AND prices.branch_id = '{$branchId}'
                    AND prices.min_quantity = 1 
                    AND prices.valid_from <= '{$nowStr}'
                    AND (prices.valid_to IS NULL OR prices.valid_to >= '{$nowStr}')
                    ORDER BY prices.priority DESC, prices.created_at DESC LIMIT 1
                ) as sorting_price")
            ])
            ->where('skus.is_active', true)
            ->where('p.is_active', true);

        // ... resto de filtros (categoría, search, sort) permanecen igual

        return $query->cursorPaginate(20)->through(function (Sku $sku) use ($cartQuantities, $now) {
            $currentQty = (int) $cartQuantities->get($sku->id, 0);
            $targetQty = $currentQty > 0 ? $currentQty : 1;
    
            // El Resolver procesa en memoria (O(1)) porque los precios ya están en la colección
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, $targetQty, $now);
            $sku->quantity_in_cart = $currentQty;
    
            return $sku; 
        });
    }
}