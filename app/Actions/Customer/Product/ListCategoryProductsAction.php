<?php

declare(strict_types=1);

namespace App\Actions\Customer\Product;

use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Http\Resources\Customer\Product\SkuResource;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class ListCategoryProductsAction
{
    /**
     * @param PriceResolverService $priceResolver Resolución financiera de precios.
     * @param GetCustomerCartAction $getCartAction Estado actual de la sesión del usuario.
     */
    public function __construct(
        private PriceResolverService $priceResolver,
        private GetCustomerCartAction $getCartAction
    ) {}

    /**
     * Ejecuta la lógica de listado con precios reactivos al carrito.
     */
    public function execute(string $categoryId, string $branchId, array $filters): CursorPaginator
    {
        $now = now()->toDateTimeString();

        // 1. CONTEXTO DE SESIÓN: Sincronización con el carrito actual
        $guestUuid = session('guest_client_uuid') ?? request()->header('X-Guest-UUID');
        $cart = $this->getCartAction->execute($guestUuid);
        $cartQuantities = collect($cart['items'])->pluck('quantity', 'sku_id');

        // 2. QUERY BASE: Eloquent + Joins para filtros y ordenamiento
        $query = Sku::query()
            // Eager Loading de precios filtrados por sucursal (Optimización N+1)
            ->with(['product.brand', 'prices' => function ($q) use ($branchId, $now) {
                $q->where('branch_id', $branchId)
                  ->where('valid_from', '<=', $now)
                  ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now));
            }])
            ->join('products as p', 'skus.product_id', '=', 'p.id')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->join('brands as b', 'p.brand_id', '=', 'b.id')
            ->select([
                'skus.*',
                'c.bg_color',
                'b.name as brand_name',
                // Subconsulta para ordenamiento por precio base (n=1)
                DB::raw("(
                    SELECT final_price FROM prices 
                    WHERE prices.sku_id = skus.id 
                    AND prices.branch_id = '{$branchId}'
                    AND prices.min_quantity = 1 
                    AND prices.valid_from <= '{$now}'
                    AND (prices.valid_to IS NULL OR prices.valid_to >= '{$now}')
                    ORDER BY prices.priority DESC, prices.created_at DESC LIMIT 1
                ) as sorting_price")
            ])
            ->where('skus.is_active', true)
            ->where('p.is_active', true);

        // 3. FILTRADO: Árbol de categorías (Padre/Hijo)
        $query->where(function ($q) use ($categoryId) {
            $q->where('p.category_id', $categoryId)
              ->orWhereIn('p.category_id', function ($sub) use ($categoryId) {
                  $sub->select('id')->from('categories')->where('parent_id', $categoryId);
              });
        });

        // 4. FILTRADO: Búsqueda textual
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('skus.name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('p.name', 'LIKE', "%{$filters['search']}%");
            });
        }

        // 5. ORDENAMIENTO
        $sort = $filters['sort'] ?? 'relevance';
        match ($sort) {
            'price_asc'  => $query->orderBy('sorting_price', 'asc'),
            'price_desc' => $query->orderBy('sorting_price', 'desc'),
            default      => $query->orderBy('skus.sort_order', 'asc')
                                  ->orderBy('p.sort_order', 'asc')
                                  ->orderBy('skus.name', 'asc')
        };

        // 6. PAGINACIÓN Y TRANSFORMACIÓN DINÁMICA
        return $query->cursorPaginate(20)->through(function (Sku $sku) use ($branchId, $cartQuantities) {
            
            // Calculamos n: cantidad actual en carrito o 1 para precio inicial
            $currentQty = $cartQuantities->get($sku->id, 0);
            $targetQty = $currentQty > 0 ? (int)$currentQty : 1;

            // Inyectamos el resultado del servicio en el modelo para el Resource
            $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, $branchId, $targetQty);
            $sku->quantity_in_cart = $currentQty;

            return (new SkuResource($sku))->resolve();
        });
    }
}