<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function __construct(
        protected PriceResolverService $priceResolver 
    ) {}

    public function execute(string $cartItemId, int $newQuantity, string $branchId): void
    {
        DB::transaction(function () use ($cartItemId, $newQuantity, $branchId) {
            // Cargamos el ítem con su SKU para poder re-calcular el precio
            $item = CartItem::with(['sku'])->findOrFail($cartItemId);

            if (!$item->is_bundle) {
                // RE-RESOLUCIÓN FINANCIERA: Si cambia la cantidad, el precio puede cambiar (Tiers)
                $priceData = $this->priceResolver->resolveWinningPrice(
                    $item->sku, 
                    $branchId, 
                    $newQuantity
                );
                
                $item->price_at_addition = $priceData->final_price;
            }

            // Actualización de cantidad sin bloqueos de stock (Soft-Cart)
            $item->quantity = $newQuantity;
            $item->save();
        });
    }
}