<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Inventory\InventoryLookupService;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;

class SyncGuestCartAction
{
    public function __construct(
        protected readonly InventoryLookupService $inventoryLookup,
        protected readonly PriceResolverService $priceResolver
    ) {}

    /**
     * Ejecuta la fusión atómica de ítems e impone las restricciones de stock y volumen.
     */
    public function execute(string $customerId, ?string $guestUuid): void
    {
        if (!$guestUuid) {
            return;
        }

        $adjustmentsMade = false;
        $now = now();

        DB::transaction(function () use ($customerId, $guestUuid, &$adjustmentsMade, $now) {
            // 1. Obtención de todos los contenedores del invitado
            $guestCarts = Cart::where('session_id', $guestUuid)->with('items.sku')->get();

            if ($guestCarts->isEmpty()) {
                return;
            }

            foreach ($guestCarts as $guestCart) {
                if ($guestCart->items->isEmpty()) {
                    $guestCart->delete();
                    continue;
                }

                $branchId = $guestCart->branch_id;

                // 2. Localización o apertura de la bandeja del usuario para esta sucursal
                $userCart = Cart::firstOrCreate([
                    'customer_id' => $customerId,
                    'branch_id'   => $branchId
                ]);

                $existingItems = CartItem::where('cart_id', $userCart->id)->get()->keyBy('sku_id');

                foreach ($guestCart->items as $guestItem) {
                    // Ignorar SKUs corruptos o dados de baja
                    if (!$guestItem->sku || !$guestItem->sku->is_active) {
                        $guestItem->delete();
                        $adjustmentsMade = true;
                        continue;
                    }

                    $existingItem = $existingItems->get($guestItem->sku_id);
                    $currentQty = $existingItem ? $existingItem->quantity : 0;
                    $combinedQty = $currentQty + $guestItem->quantity;

                    // 3. Validación de Stock Real de la Sucursal en la fusión
                    $availableStock = $this->inventoryLookup->getAvailableStock($guestItem->sku_id, $branchId);
                    
                    // Cálculo del techo normativo (Mínimo entre el Stock y el tope de la plataforma de 99 unidades)
                    $maxAllowedQty = min(99, $availableStock);
                    $finalQty = min($combinedQty, $maxAllowedQty);

                    if ($finalQty <= 0) {
                        // Si no hay existencias disponibles, el ítem no se fusiona
                        $guestItem->delete();
                        $adjustmentsMade = true;
                        continue;
                    }

                    if ($finalQty < $combinedQty) {
                        $adjustmentsMade = true;
                    }

                    // 4. Recálculo continuo del precio por volumen de la sucursal activa
                    $priceData = $this->priceResolver->resolveWinningPrice($guestItem->sku, $finalQty, $branchId, $now);

                    if ($existingItem) {
                        $existingItem->update([
                            'quantity'          => $finalQty,
                            'price_at_addition' => $priceData->final_price
                        ]);
                        $guestItem->delete();
                    } else {
                        $guestItem->update([
                            'cart_id'           => $userCart->id,
                            'quantity'          => $finalQty,
                            'price_at_addition' => $priceData->final_price
                        ]);
                    }
                }

                $guestCart->delete();
            }
        });

        // 5. PROTOCOLO DE HIGIENE: El Action purga de forma soberana el rastro de la sesión
        session()->forget('guest_client_uuid');

        // 6. GESTIÓN DE NOTIFICACIONES AL CLIENTE
        if ($adjustmentsMade) {
            session()->flash('warning', 'Tu carrito de invitado ha sido unificado. Algunos artículos sufrieron cambios en sus cantidades o precios debido a la disponibilidad de la sucursal.');
        } else {
            session()->flash('success', 'Tu carrito se ha sincronizado correctamente.');
        }
    }
}