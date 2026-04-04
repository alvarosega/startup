<?php
//ok
namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;

class GetCustomerCartAction
{
    public function __construct(private PriceResolverService $priceResolver) {}

    public function execute(?string $guestUuid, ?string $customerId, string $branchId): array
    {
        $now = now();
    
        $cart = Cart::query()
            ->select(['id', 'branch_id', 'session_id', 'customer_id'])
            ->where('branch_id', $branchId)
            ->where(fn($q) => $customerId 
                ? $q->where('customer_id', $customerId) 
                : $q->where('session_id', $guestUuid)
            )
            ->with([
                'items' => fn($q) => $q->select(['id', 'cart_id', 'sku_id', 'quantity', 'is_bundle']),
                'items.sku' => fn($q) => $q->select(['skus.id', 'product_id', 'name', 'image_path', 'base_price']) // AÑADIR base_price
                    ->leftJoin('inventory_balances as ib', function($j) use ($branchId) {
                        $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                    })
                    ->addSelect(['ib.total_physical', 'ib.total_reserved']),

                'items.sku.prices' => fn($q) => $q->where('branch_id', $branchId)
                    ->where('valid_from', '<=', $now)
                    ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now)), // FILTRO TÁCTICO
                'items.sku.product' => fn($q) => $q->select(['id', 'name', 'brand_id']),
                'items.sku.product.brand' => fn($q) => $q->select(['id', 'name']),
                'items.sku.prices' => fn($q) => $q->where('branch_id', $branchId)
            ])
            ->first();
    
        if (!$cart) return $this->emptyCartResponse();
    
        // HIDRATACIÓN EN RAM: Incluimos el total_safety en el cálculo
        $cart->items->each(function ($item) use ($now) {
            $sku = $item->sku;
            
            // RECTIFICACIÓN: Restamos total_safety para que el carrito 
            // sea honesto con lo que el Checkout permitirá procesar.
            $item->max_stock = max(0, 
                (int)($sku->total_physical ?? 0) - 
                (int)($sku->total_reserved ?? 0) - 
                (int)($sku->total_safety ?? 0)
            );
            
            $item->current_price_data = $this->priceResolver->resolveWinningPrice($sku, $item->quantity, $now);
        });
        return (new \App\Http\Resources\Customer\Cart\CartResource($cart))->resolve();
    }
    private function emptyCartResponse(): array 
    {
        return [
            'id'            => null,
            'items'         => [],
            'total_items'   => 0,
            'total_price'   => 0,
            'total_savings' => 0
        ];
    }
}