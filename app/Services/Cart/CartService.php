<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\{Cart, CartItem, Sku, InventoryLot};
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\{DB, Auth, Log};
use Carbon\Carbon;
use App\Services\Inventory\InventoryLookupService;

class CartService
{
    public function __construct(
        protected ShopContextService $shopContext,
        protected PriceResolverService $priceResolver,
        protected InventoryLookupService $inventoryLookup // <--- INYECCIÓN DE DEPENDENCIA
    ) {}

    /**
     * Gestión atómica de ítems en el carrito con validación de stock real.
     */
    public function addSku(string $skuId, int $quantity, ?string $guestUuid = null, bool $isAbsolute = false): object
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $now = now();

        return DB::transaction(function () use ($skuId, $quantity, $branchId, $guestUuid, $isAbsolute, $now) {
            $cart = $this->getOrCreateCart($branchId, $guestUuid);
            
            // 1. Obtención de SKU con precios filtrados por sucursal
            $sku = Sku::with(['prices' => function ($q) use ($branchId, $now) {
                $q->where('branch_id', $branchId)
                  ->where('valid_from', '<=', $now)
                  ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now));
            }])->findOrFail($skuId);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->whereNull('bundle_id')
                ->first();

            // 2. Cálculo de volumen proyectado
            $currentQty = $item ? $item->quantity : 0;
            $newTotalQuantity = $isAbsolute ? $quantity : ($currentQty + $quantity);

            if ($newTotalQuantity <= 0) {
                $item?->delete();
                return (object) ['success' => true, 'code' => 'REMOVED', 'message' => 'Ítem eliminado.'];
            }

            // 3. VALIDACIÓN DE STOCK (Sin reserva física)
            $availableStock = $this->getVisibleStock($sku->id, $branchId);
            
            if ($newTotalQuantity > $availableStock) {
                return (object) [
                    'success' => false,
                    'code'    => 'INSUFFICIENT_STOCK',
                    'message' => "Stock insuficiente. Máximo disponible: {$availableStock}",
                    'meta'    => ['available' => $availableStock]
                ];
            }

            // 4. RESOLUCIÓN DE PRECIO DINÁMICO
            $priceData = $this->priceResolver->resolveWinningPrice($sku, (int) $newTotalQuantity, $now);

            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'sku_id' => $sku->id, 'bundle_id' => null],
                [
                    'quantity'          => $newTotalQuantity,
                    'price_at_addition' => $priceData->final_price,
                    'is_bundle'         => false
                ]
            );

            return (object) [
                'success' => true, 
                'code'    => 'OK', 
                'message' => 'Carrito actualizado.',
                'meta'    => ['quantity' => $newTotalQuantity, 'unit_price' => $priceData->final_price]
            ];
        });
    }

    public function fusionGuestCart(string $customerId, string $guestUuid): void
    {
        DB::transaction(function () use ($customerId, $guestUuid) {
            // RECTIFICACIÓN CRÍTICA (OPCIÓN A): Buscamos TODOS los carritos del Guest sin importar la sucursal.
            $guestCarts = Cart::where('session_id', $guestUuid)->with('items')->get();
            
            if ($guestCarts->isEmpty()) return;

            foreach ($guestCarts as $guestCart) {
                // Si el carrito estaba vacío, simplemente lo destruimos y continuamos.
                if ($guestCart->items->isEmpty()) {
                    $guestCart->delete();
                    continue;
                }

                // REGLA DE HIERRO: El carrito del usuario heredará la sucursal exacta donde el guest lo armó.
                $originalBranchId = $guestCart->branch_id;

                // 1. Crear o recuperar el carrito del Usuario para ESA sucursal específica
                $userCart = Cart::firstOrCreate([
                    'customer_id' => $customerId, 
                    'branch_id' => $originalBranchId
                ]);
                
                // 2. Cargar items existentes del usuario para comparación en RAM
                $existingItems = CartItem::where('cart_id', $userCart->id)->get()->keyBy('sku_id');

                foreach ($guestCart->items as $guestItem) {
                    $existingItem = $existingItems->get($guestItem->sku_id);

                    if ($existingItem) {
                        // Fusión de cantidades respetando el límite de 99
                        $existingItem->update(['quantity' => min(99, $existingItem->quantity + $guestItem->quantity)]);
                        $guestItem->delete();
                    } else {
                        // Traspaso de propiedad manteniendo la integridad del SKU
                        $guestItem->update(['cart_id' => $userCart->id]);
                    }
                }
                
                // 3. Eliminar el contenedor temporal del Guest
                $guestCart->delete();
            }
        });
    }

    /**
     * Consulta de stock físico neto (Físico - Reservado).
     */
    public function getVisibleStock(string $skuId, string $branchId): int
    {
        return $this->inventoryLookup->getAvailableStock($skuId, $branchId);
    }

    /**
     * Localizador de instancia de carrito.
     */
    protected function getOrCreateCart(string $branchId, ?string $guestUuid = null): Cart
    {
        $customerId = Auth::guard('customer')->id();
        // Prioridad: Parámetro > Sesión de Laravel
        $sessionId = $guestUuid ?? session('guest_client_uuid');

        if ($customerId) {
            return Cart::firstOrCreate(['customer_id' => $customerId, 'branch_id' => $branchId]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId, 'branch_id' => $branchId]);
    }
}