<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\{Cart, CartItem, Sku};
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use App\Services\Inventory\InventoryLookupService;
use Illuminate\Support\Facades\{DB, Auth};

class CartService
{
    public function __construct(
        protected readonly ShopContextService $shopContext,
        protected readonly PriceResolverService $priceResolver,
        protected readonly InventoryLookupService $inventoryLookup
    ) {}

    /**
     * Inserción y actualización atómica de ítems en el carrito con recálculo financiero continuo.
     */
    public function addSku(string $skuId, int $quantity, ?string $guestUuid = null, bool $isAbsolute = false): object
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $now = now();

        return DB::transaction(function () use ($skuId, $quantity, $branchId, $guestUuid, $isAbsolute, $now) {
            $cart = $this->getOrCreateCart($branchId, $guestUuid);
            
            $sku = Sku::where('id', $skuId)->where('is_active', true)->firstOrFail();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->whereNull('bundle_id')
                ->first();

            // Cálculo dinámico del volumen proyectado (Absoluto vs Relativo)
            $currentQty = $item ? $item->quantity : 0;
            $newTotalQuantity = $isAbsolute ? $quantity : ($currentQty + $quantity);

            if ($newTotalQuantity <= 0) {
                $item?->delete();
                return (object) ['success' => true, 'code' => 'REMOVED', 'message' => 'Artículo removido del carrito.'];
            }

            // LEY: Validación pesimista de disponibilidad de Stock Neto (Físico - Reservas)
            $availableStock = $this->inventoryLookup->getAvailableStock($sku->id, $branchId);
            
            if ($newTotalQuantity > $availableStock) {
                return (object) [
                    'success' => false,
                    'code'    => 'INSUFFICIENT_STOCK',
                    'message' => "Stock insuficiente en almacén. Disponibilidad neta actual: {$availableStock}",
                    'meta'    => ['available' => $availableStock]
                ];
            }

            // LEY: Recálculo en caliente del precio unitario óptimo basado en la cantidad proyectada
            $priceData = $this->priceResolver->resolveWinningPrice($sku, $newTotalQuantity, $branchId, $now);

            // Blindaje de Seguridad: Si el precio es cero, el producto no está habilitado comercialmente
            if ((float) $priceData->final_price === 0.00) {
                return (object) [
                    'success' => false,
                    'code'    => 'PRICE_UNRESOLVED_RESTRICITON',
                    'message' => 'El artículo seleccionado no se encuentra disponible para la venta en esta sucursal.'
                ];
            }

            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'sku_id' => $sku->id, 'bundle_id' => null],
                [
                    'quantity'          => $newTotalQuantity,
                    'price_at_addition' => $priceData->final_price, // Sobreescribe con la escala ganadora
                    'is_bundle'         => false
                ]
            );

            return (object) [
                'success' => true, 
                'code'    => 'OK', 
                'message' => 'Carrito actualizado correctamente.',
                'meta'    => ['quantity' => $newTotalQuantity, 'unit_price' => $priceData->final_price]
            ];
        });
    }

    /**
     * Fusiona los carritos temporales recolectados como invitado hacia la cuenta del cliente.
     */
    public function fusionGuestCart(string $customerId, string $guestUuid): void
    {
        DB::transaction(function () use ($customerId, $guestUuid) {
            $guestCarts = Cart::where('session_id', $guestUuid)->with('items')->get();
            
            if ($guestCarts->isEmpty()) return;

            foreach ($guestCarts as $guestCart) {
                if ($guestCart->items->isEmpty()) {
                    $guestCart->delete();
                    continue;
                }

                $originalBranchId = $guestCart->branch_id;

                $userCart = Cart::firstOrCreate([
                    'customer_id' => $customerId, 
                    'branch_id'   => $originalBranchId
                ]);
                
                $existingItems = CartItem::where('cart_id', $userCart->id)->get()->keyBy('sku_id');

                foreach ($guestCart->items as $guestItem) {
                    $existingItem = $existingItems->get($guestItem->sku_id);

                    if ($existingItem) {
                        $existingItem->update(['quantity' => min(99, $existingItem->quantity + $guestItem->quantity)]);
                        $guestItem->delete();
                    } else {
                        $guestItem->update(['cart_id' => $userCart->id]);
                    }
                }
                
                $guestCart->delete();
            }
        });
    }

    /**
     * Localizador o creador de instancias de persistencia de Carrito.
     */
    protected function getOrCreateCart(string $branchId, ?string $guestUuid = null): Cart
    {
        $customerId = Auth::guard('customer')->id();
        $sessionId = $guestUuid ?? session('guest_client_uuid');

        if ($customerId) {
            return Cart::firstOrCreate(['customer_id' => $customerId, 'branch_id' => $branchId]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId, 'branch_id' => $branchId]);
    }
}