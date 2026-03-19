<?php

namespace App\Services\Cart;

use App\Models\{Cart, CartItem, Sku, Bundle, InventoryLot};
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\{DB, Auth};
use Exception;

class CartService
{
    public function __construct(
        protected PriceResolverService $priceResolver,
        protected ShopContextService $shopContext
    ) {}

    /**
     * @param array $customItems Opcional: [sku_id => quantity] para editables
     */
    public function addBundle(string $bundleId, int $requestedQty = 1, array $customItems = [], ?string $guestUuid = null): void
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $bundle = Bundle::with('skus')->findOrFail($bundleId);
    
        DB::transaction(function () use ($bundle, $requestedQty, $branchId, $customItems, $guestUuid) {
            $this->validateBundleStock($bundle, $requestedQty, $branchId, $customItems);
    
            if ($bundle->is_editable) {
                $this->processEditableBundle($bundle, $requestedQty, $branchId, $customItems, $guestUuid);
            } else {
                $this->processAtomicBundle($bundle, $requestedQty, $branchId, $guestUuid);
            }
        });
    }

    protected function processAtomicBundle(Bundle $bundle, int $quantity, string $branchId, ?string $guestUuid = null): void
    {
        $cart = $this->getOrCreateCart($branchId, $guestUuid);
    
        $item = CartItem::where('cart_id', $cart->id)
            ->where('bundle_id', $bundle->id)
            ->first();
    
        // Calculamos la cantidad total que habría en el carrito
        $newTotalQuantity = ($item ? $item->quantity : 0) + $quantity;
    
        // Validación de stock waterfall (usando el método corregido abajo)
        $this->validateBundleStock($bundle, $newTotalQuantity, $branchId);
    
        if ($newTotalQuantity > ($bundle->max_quantity_per_order ?? 5)) {
            throw new Exception("Límite excedido. Máximo " . ($bundle->max_quantity_per_order ?? 5) . " unidades.");
        }
    
        CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'bundle_id' => $bundle->id],
            [
                'quantity' => $newTotalQuantity,
                'price_at_addition' => $bundle->fixed_price, 
                'is_bundle' => true,
                'sku_id' => null 
            ]
        );
    }

    protected function processEditableBundle(Bundle $bundle, int $requestedQty, string $branchId, array $customItems = [], ?string $guestUuid = null): void
    {
        $cart = $this->getOrCreateCart($branchId, $guestUuid);
    
        foreach ($bundle->skus as $sku) { 
            $qtyPerBundle = $customItems[$sku->id] ?? $sku->pivot->quantity;
            $totalSkuQty = $qtyPerBundle * $requestedQty;
    
            if ($totalSkuQty <= 0) continue;
    
            // Buscamos si ya existe para calcular el precio sobre el TOTAL
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->whereNull('bundle_id')
                ->first();
    
            $newTotalQuantity = ($existing ? $existing->quantity : 0) + $totalSkuQty;
    
            // Resolvemos precio basado en la nueva cantidad total (Tier Alert!)
            $priceData = $this->priceResolver->resolveWinningPrice($sku, $branchId, $newTotalQuantity);
    
            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'sku_id' => $sku->id, 'bundle_id' => null],
                [
                    'quantity' => $newTotalQuantity,
                    'price_at_addition' => $priceData->final_price,
                    'is_bundle' => false
                ]
            );
        }
    }

    public function validateBundleStock(Bundle $bundle, int $bundleQty, string $branchId, array $customItems = []): void
    {
        foreach ($bundle->skus as $sku) {
            // Si no hay customItems (atómico), usamos el pivot de la receta
            $qtyPerBundle = $customItems[$sku->id] ?? $sku->pivot->quantity;
            $required = $qtyPerBundle * $bundleQty;
    
            if ($required <= 0) continue;
            
            $available = $this->getSkuAvailableStock($sku->id, $branchId);
    
            if ($available < $required) {
                throw new Exception("Stock insuficiente: {$sku->name} (Disponible: $available)");
            }
        }
    }
    private function getSkuAvailableStock(string $skuId, string $branchId): int
    {
        return InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('is_safety_stock', false)
            ->selectRaw('SUM(quantity - reserved_quantity) as total')
            ->value('total') ?? 0;
    }

    protected function getOrCreateCart(string $branchId, ?string $guestUuid = null): Cart
    {
        $customerId = Auth::guard('customer')->id();
        
        // Si no hay customerId, usamos el guestUuid que viene del localStorage (vía DTO)
        // Solo si ambos fallan, caemos en el session id nativo.
        $sessionId = $guestUuid ?? session()->getId();
    
        return Cart::firstOrCreate(
            $customerId 
                ? ['customer_id' => $customerId, 'branch_id' => $branchId]
                : ['session_id' => $sessionId, 'branch_id' => $branchId]
        );
    }

    // Añadir este método al final de tu CartService.php

    public function addSku(string $skuId, int $quantity, ?string $guestUuid = null): void
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $sku = Sku::findOrFail($skuId);

        DB::transaction(function () use ($sku, $quantity, $branchId, $guestUuid) {
            $cart = $this->getOrCreateCart($branchId, $guestUuid);

            // 1. Buscamos si ya existe para calcular el TOTAL (Crucial para Tiers)
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->whereNull('bundle_id')
                ->first();

            $newTotalQuantity = ($existingItem ? $existingItem->quantity : 0) + $quantity;

            // 2. Validación de Stock Real
            $available = $this->getSkuAvailableStock($sku->id, $branchId);
            if ($available < $newTotalQuantity) {
                throw new \Exception("Stock insuficiente. Disponible: $available unidades.");
            }

            // 3. Resolución de Precio Dinámico (Garantiza el campo NOT NULL)
            $priceData = $this->priceResolver->resolveWinningPrice($sku, $branchId, $newTotalQuantity);

            // 4. UPSERT de la línea del carrito
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
}