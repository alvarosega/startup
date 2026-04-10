<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Services\Finance\PriceResolverService;
use App\Http\Resources\Customer\Cart\CartResource;

class GetCustomerCartAction
{
    public function __construct(private PriceResolverService $priceResolver) {}

    public function execute(?string $guestUuid, ?string $customerId, string $branchId): CartResource
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
                'items' => fn($q) => $q->select(['id', 'cart_id', 'sku_id', 'bundle_id', 'quantity', 'is_bundle']),
                'items.bundle' => fn($q) => $q->select(['id', 'name', 'image_path', 'fixed_price']),
                'items.bundle.skus' => fn($q) => $q->select(['skus.id', 'name']),
                'items.sku' => fn($q) => $q->select(['skus.id', 'product_id', 'name', 'image_path', 'base_price']) 
                    ->leftJoin('inventory_balances as ib', function($j) use ($branchId) {
                        $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                    })
                    ->addSelect(['ib.total_physical', 'ib.total_reserved', 'ib.total_safety']),

                'items.sku.product' => fn($q) => $q->select(['id', 'name', 'brand_id']),
                'items.sku.product.brand' => fn($q) => $q->select(['id', 'name']),
                'items.sku.prices' => fn($q) => $q->where('branch_id', $branchId)
                    ->where('valid_from', '<=', $now)
                    ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now))
            ])
            ->first();
    

        if (!$cart) {
            // Creamos una instancia de Cart vacía en RAM para que el Resource la procese
            $emptyCart = new \App\Models\Cart();
            $emptyCart->setRelation('items', collect());
            $emptyCart->calculated_total_items = 0;
            $emptyCart->calculated_total_price = 0.0;
            $emptyCart->calculated_total_savings = 0.0;
            
            return new CartResource($emptyCart);
        }
            
        $totalItems = 0;
        $totalPrice = 0.0;
        $totalSavings = 0.0;

        // MODIFICAR: Bloque de hidratación en RAM
        $cart->items->each(function ($item) use ($now, &$totalItems, &$totalPrice, &$totalSavings) {
            if ($item->is_bundle && $item->bundle) {
                // Lógica para Bundles (Tratado como SKU individual según tu requerimiento)
                $item->max_stock = 99; // Límite virtual para packs
                $item->current_price_data = (object)[
                    'final_price' => (float) $item->bundle->fixed_price,
                    'list_price'  => (float) $item->bundle->fixed_price,
                    'type'        => 'bundle',
                    'next_tier'   => null
                ];
            } else {
                // Lógica existente para SKUs
                $sku = $item->sku;
                $item->max_stock = max(0, (int)($sku->total_physical ?? 0) - (int)($sku->total_reserved ?? 0) - (int)($sku->total_safety ?? 0));
                $item->current_price_data = $this->priceResolver->resolveWinningPrice($sku, $item->quantity, $now);
            }

            $priceData = $item->current_price_data;
            $totalItems += $item->quantity;
            $totalPrice += ($item->quantity * $priceData->final_price);
            $totalSavings += ($item->quantity * ($priceData->list_price - $priceData->final_price));
        });
        // RECTIFICACIÓN CRÍTICA: values() resetea los índices para asegurar un JSON Array []
        $cart->setRelation('items', $cart->items->values()); 

        $cart->calculated_total_items = $totalItems;
        $cart->calculated_total_price = $totalPrice;
        $cart->calculated_total_savings = $totalSavings;

        return new CartResource($cart);
    }
}