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

    /**
     * Ejecuta el motor de búsqueda y listado de SKUs enriquecidos.
     */
    public function execute(string $categoryId, string $branchId, array $filters): CursorPaginator
    {
        $now = now();
        $nowStr = $now->toDateTimeString();

        // 1. CONTEXTO DE CARRITO (Para Upsell en tiempo real)
        $guestUuid = session('guest_client_uuid') ?? request()->header('X-Guest-UUID');
        $cart = $this->getCartAction->execute($guestUuid);
        $cartQuantities = collect($cart['items'] ?? [])->pluck('quantity', 'sku_id');

        // 2. CONSTRUCCIÓN DE QUERY SEGURA
        $query = Sku::query()
            ->with(['product.brand', 'prices' => function ($q) use ($branchId, $nowStr) {
                $q->where('branch_id', $branchId)
                  ->where('valid_from', '<=', $nowStr)
                  ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $nowStr));
            }])
            ->join('products as p', 'skus.product_id', '=', 'p.id')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->join('brands as b', 'p.brand_id', '=', 'b.id')
            ->select([
                'skus.*',
                'c.bg_color',
                'b.name as brand_name',
                // Subconsulta optimizada para el precio de ordenamiento
                DB::raw("(
                    SELECT final_price FROM prices 
                    WHERE prices.sku_id = skus.id 
                    AND prices.branch_id = '{$branchId}'
                    AND prices.min_quantity = 1 
                    AND prices.valid_from <= '{$nowStr}'
                    AND (prices.valid_to IS NULL OR prices.valid_to >= '{$nowStr}')
                    ORDER BY prices.priority DESC, prices.created_at DESC LIMIT 1
                ) as sorting_price"),
                // Subconsulta de stock disponible (Cálculo atómico)
                DB::raw("(
                    SELECT SUM(quantity - reserved_quantity) FROM inventory_lots 
                    WHERE inventory_lots.sku_id = skus.id 
                    AND inventory_lots.branch_id = '{$branchId}'
                    AND inventory_lots.is_safety_stock = 0
                ) as available_stock")
            ])
            ->where('skus.is_active', true)
            ->where('p.is_active', true);

        // 3. FILTRADO POR ÁRBOL DE CATEGORÍA
        $query->where(function ($q) use ($categoryId) {
            $q->where('p.category_id', $categoryId)
              ->orWhereIn('p.category_id', function ($sub) use ($categoryId) {
                  $sub->select('id')->from('categories')->where('parent_id', $categoryId);
              });
        });

        // 4. BÚSQUEDA
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('skus.name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('p.name', 'LIKE', "%{$filters['search']}%");
            });
        }

        // 5. ORDENAMIENTO DINÁMICO
        $sort = $filters['sort'] ?? 'relevance';
        match ($sort) {
            'price_asc'  => $query->orderBy('sorting_price', 'asc'),
            'price_desc' => $query->orderBy('sorting_price', 'desc'),
            default      => $query->orderBy('skus.sort_order', 'asc')
                                  ->orderBy('p.sort_order', 'asc')
                                  ->orderBy('skus.name', 'asc')
        };

        // 6. PAGINACIÓN Y ENRIQUECIMIENTO (Devuelve Modelos, no Resources)
        return $query->cursorPaginate(20)->through(function (Sku $sku) use ($cartQuantities, $now) {
            $currentQty = (int) $cartQuantities->get($sku->id, 0);
            $targetQty = $currentQty > 0 ? $currentQty : 1;

            // Inyectamos la verdad calculada en el modelo
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, $targetQty, $now);
            $sku->quantity_in_cart = $currentQty;

            return $sku; 
        });
    }
}