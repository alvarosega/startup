<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\{Cart, CartItem, Sku, InventoryLot};
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\{DB, Auth, Log};
use Carbon\Carbon;

class CartService
{
    public function __construct(
        protected ShopContextService $shopContext,
        protected PriceResolverService $priceResolver
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
        $branchId = $this->shopContext->getActiveBranchId(); // Contexto de la rama actual
        $now = now();

        DB::transaction(function () use ($customerId, $guestUuid, $branchId, $now) {
            // ANCLA DE SEGURIDAD: Solo buscamos el carrito de invitado de LA MISMA sucursal
            $guestCart = Cart::where('session_id', $guestUuid)
                ->where('branch_id', $branchId)
                ->with('items')
                ->first();

            if (!$guestCart || $guestCart->items->isEmpty()) return;
            $customerCart = $this->getOrCreateCart($branchId);

            foreach ($guestCart->items as $guestItem) {
                $availableStock = $this->getVisibleStock($guestItem->sku_id, $branchId);
                
                $existingItem = CartItem::where('cart_id', $customerCart->id)
                    ->where('sku_id', $guestItem->sku_id)
                    ->first();

                // Sumamos cantidades
                $targetQty = ($existingItem ? $existingItem->quantity : 0) + $guestItem->quantity;
                
                // REGLA 3: Fusionamos hasta el tope de stock disponible
                $finalQty = (int) min($targetQty, $availableStock);

                if ($finalQty > 0) {
                    $sku = Sku::with(['prices' => function ($q) use ($branchId, $now) {
                        $q->where('branch_id', $branchId)
                          ->where('valid_from', '<=', $now)
                          ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now));
                    }])->find($guestItem->sku_id);

                    if ($sku) {
                        $priceData = $this->priceResolver->resolveWinningPrice($sku, $finalQty, $now);

                        CartItem::updateOrCreate(
                            ['cart_id' => $customerCart->id, 'sku_id' => $sku->id],
                            [
                                'quantity'          => $finalQty,
                                'price_at_addition' => $priceData->final_price,
                                'is_bundle'         => false
                            ]
                        );
                    }
                }
            }

            // Limpiamos el carrito de invitado una vez procesado
            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    /**
     * Consulta de stock físico neto (Físico - Reservado).
     */
    public function getVisibleStock(string $skuId, string $branchId): int
    {
        return (int) InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('is_safety_stock', false)
            ->selectRaw('SUM(quantity - reserved_quantity) as total')
            ->value('total') ?? 0;
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