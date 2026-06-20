<?php

namespace App\Actions\Customer\Shop;

use App\Models\Sku;
use App\Models\InventoryLot;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class GetShopSkusAction
{
    public function __construct(
        protected ShopContextService $shopContext
    ) {}

    /**
     * Obtiene SKUs con stock calculado y precios filtrados para la sucursal activa.
     * * @param array $productIds IDs de los productos a buscar (obtenidos de la categoría/zona)
     * @return Collection
     */
    // app/Actions/Customer/Shop/GetShopSkusAction.php
    public function execute(array $productIds): Collection
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $user = Auth::guard('customer')->user();
        $allowedPriceTypes = ['regular', 'offer', 'liquidation']; 
        if ($user) $allowedPriceTypes[] = 'member'; 

        return Sku::query()
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->addSelect([
                'available_stock' => InventoryLot::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $branchId)
                    ->where('is_safety_stock', false)
            ])
            // CAMBIO: Usamos 'prices' como relación base para 'all_prices' en el frontend
            ->with(['prices' => function ($q) use ($branchId, $allowedPriceTypes) {
                $q->where('branch_id', $branchId)
                ->whereIn('type', $allowedPriceTypes)
                ->where('valid_from', '<=', now())
                ->where(fn($i) => $i->whereNull('valid_to')->orWhere('valid_to', '>=', now()));
            }])
            ->with(['product.brand', 'product.category'])
            ->get()
            ->map(function ($sku) {
                $sku->stock_status = $sku->available_stock > 0 ? 'in_stock' : 'out_of_stock';
                // Mapeamos para el componente Vue
                $sku->all_prices = $sku->prices; 
                $sku->price = (float) $sku->base_price;
                return $sku;
            });
    }
}