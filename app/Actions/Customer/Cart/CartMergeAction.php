<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;

class CartMergeAction
{
    public function __construct(
        protected PriceResolverService $priceResolver
    ) {}

    /**
     * Fusiona el carrito de invitado con el del cliente autenticado.
     */
    public function execute(string $guestUuid, string $customerId): void
    {
        DB::transaction(function () use ($guestUuid, $customerId) {
            // 1. Identificar carritos de origen (Guest)
            $guestCarts = Cart::where('session_id', $guestUuid)
                ->whereNull('customer_id')
                ->with('items.sku')
                ->get();

            if ($guestCarts->isEmpty()) {
                return;
            }

            foreach ($guestCarts as $guestCart) {
                // 2. Obtener o crear carrito de destino para la sucursal específica
                $customerCart = Cart::firstOrCreate([
                    'customer_id' => $customerId,
                    'branch_id'   => $guestCart->branch_id
                ]);

                foreach ($guestCart->items as $item) {
                    $this->mergeItem($item, $customerCart);
                }

                // 3. Purga: Eliminación del rastro anónimo (Atomic Cleanup)
                $guestCart->forceDelete();
            }
        });
    }

    /**
     * Procesa la migración de una línea individual con recalculo de Tiers.
     */
    protected function mergeItem(CartItem $guestItem, Cart $customerCart): void
    {
        // Localizar si el SKU ya existe en el carrito real
        $existingItem = CartItem::where('cart_id', $customerCart->id)
            ->where('sku_id', $guestItem->sku_id)
            ->whereNull('bundle_id')
            ->first();

        $newTotalQuantity = ($existingItem ? $existingItem->quantity : 0) + $guestItem->quantity;

        // REGLA FINANCIERA: El precio del invitado no es válido para el cliente autenticado.
        // Se debe re-resolver el precio ganador basado en el nuevo contexto del usuario.
        $priceData = $this->priceResolver->resolveWinningPrice(
            $guestItem->sku, 
            $customerCart->branch_id, 
            $newTotalQuantity
        );

        CartItem::updateOrCreate(
            ['cart_id' => $customerCart->id, 'sku_id' => $guestItem->sku_id, 'bundle_id' => null],
            [
                'quantity'          => $newTotalQuantity,
                'price_at_addition' => $priceData->final_price,
                'is_bundle'         => $guestItem->is_bundle
            ]
        );
    }
}