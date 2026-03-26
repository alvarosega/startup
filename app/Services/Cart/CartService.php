<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\{Cart, CartItem, Sku, InventoryLot, Bundle};
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\{DB, Auth};

class CartService
{
    public function __construct(
        protected PriceResolverService $priceResolver,
        protected ShopContextService $shopContext
    ) {}

    // Cambia la firma del método para aceptar el flag de "cantidad absoluta"
    public function addSku(string $skuId, int $quantity, ?string $guestUuid = null, bool $isAbsolute = false): void
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $sku = Sku::findOrFail($skuId);

        DB::transaction(function () use ($sku, $quantity, $branchId, $guestUuid, $isAbsolute) {
            $cart = $this->getOrCreateCart($branchId, $guestUuid);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->whereNull('bundle_id')
                ->first();

            // CIRUGÍA: Si es absoluto (Template), usamos $quantity. Si no, sumamos.
            $newTotalQuantity = $isAbsolute ? $quantity : (($item ? $item->quantity : 0) + $quantity);

            // REGLA DE LIMPIEZA: Si la cantidad llega a 0, el ítem sale del carrito
            if ($newTotalQuantity <= 0) {
                $item?->delete();
                return;
            }

            // El PriceResolver ahora recibe el volumen REAL final para aplicar el Tier correcto
            $priceData = $this->priceResolver->resolveWinningPrice($sku, $branchId, $newTotalQuantity);

            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'sku_id' => $sku->id, 'bundle_id' => null],
                [
                    'quantity'          => $newTotalQuantity,
                    'price_at_addition' => $priceData->final_price,
                    'is_bundle'         => false
                ]
            );
        });
    }

    // El método de stock se mantiene por si el frontend lo pide para el "Radar" visual, 
    // pero ya no bloquea la lógica de negocio.
    public function getVisibleStock(string $skuId, string $branchId): int
    {
        return (int) InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('is_safety_stock', false)
            ->selectRaw('SUM(quantity - reserved_quantity) as total')
            ->value('total') ?? 0;
    }

    protected function getOrCreateCart(string $branchId, ?string $guestUuid = null): Cart
    {
        $customerId = Auth::guard('customer')->id();
        $sessionId = $guestUuid ?? session()->getId();

        return Cart::firstOrCreate(
            $customerId 
                ? ['customer_id' => $customerId, 'branch_id' => $branchId]
                : ['session_id' => $sessionId, 'branch_id' => $branchId]
        );
    }
    public function addBundle(string $bundleId, int $requestedQty = 1, array $customQuantities = [], ?string $guestUuid = null): void
    {
        // Ahora que importamos Bundle, esta línea ya no fallará
        $bundle = Bundle::with('skus')->findOrFail($bundleId);
    
        if ($bundle->type === 'template') {
            DB::transaction(function () use ($bundle, $customQuantities, $guestUuid) {
                foreach ($bundle->skus as $sku) {
                    // Si es template, forzamos cantidad absoluta (isAbsolute = true)
                    $finalQty = isset($customQuantities[$sku->id]) 
                        ? (int)$customQuantities[$sku->id] 
                        : (int)$sku->pivot->quantity;
                    
                    $this->addSku($sku->id, $finalQty, $guestUuid, true);
                }
            });
        } else {
            // Silo para Packs Atómicos (No editables)
            $this->processAtomicBundle($bundle, $requestedQty, $guestUuid);
        }
    }
}