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

    public function execute(string $categoryId, string $branchId, array $filters): CursorPaginator
    {
        $nowStr = now()->toDateTimeString();
    
        // 1. Contexto de carrito
        $guestUuid = session('guest_client_uuid') ?? request()->header('X-Guest-UUID');
        $cart = $this->getCartAction->execute($guestUuid);
        $cartQuantities = collect($cart['items'] ?? [])->pluck('quantity', 'sku_id');
    

        $query = Sku::query()
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
                // RECTIFICACIÓN: Traemos las columnas necesarias para el Accessor del Modelo
                DB::raw('COALESCE(ib.total_physical, 0) as total_physical'),
                DB::raw('COALESCE(ib.total_reserved, 0) as total_reserved'),
                // Subconsulta para el precio de ordenamiento
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

        // 3. FILTRADO POR CATEGORÍA (Incluye hijos)
        $query->where(function ($q) use ($categoryId) {
            $q->where('p.category_id', $categoryId)
              ->orWhereIn('p.category_id', function ($sub) use ($categoryId) {
                  $sub->select('id')->from('categories')->where('parent_id', $categoryId);
              });
        });

        // 4. BÚSQUEDA FULLTEXT (Opcional según tu migración)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('skus.name', 'LIKE', "%{$search}%")
                  ->orWhere('p.name', 'LIKE', "%{$search}%");
            });
        }

        // 5. ORDENAMIENTO
        $sort = $filters['sort'] ?? 'relevance';
        $query = match ($sort) {
            'price_asc'  => $query->orderBy('sorting_price', 'asc'),
            'price_desc' => $query->orderBy('sorting_price', 'desc'),
            default      => $query->orderBy('skus.sort_order', 'asc')
                                  ->orderBy('skus.name', 'asc')
        };

        return $query->cursorPaginate(20)->through(function (Sku $sku) use ($cartQuantities) {
            $currentQty = (int) $cartQuantities->get($sku->id, 0);
            $targetQty = $currentQty > 0 ? $currentQty : 1;
    
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, $targetQty, now());
            $sku->quantity_in_cart = $currentQty;
    
            return $sku; 
        });
    }
}