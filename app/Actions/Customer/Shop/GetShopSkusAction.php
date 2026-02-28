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
    public function execute(array $productIds): Collection
    {
        // 1. Resolver Contexto (Sucursal y Usuario)
        $branchId = $this->shopContext->getActiveBranchId();
        $user = Auth::guard('customer')->user();

        // 2. Definir Tipos de Precio permitidos
        // Regla: 'regular', 'offer', 'liquidation' son públicos. 
        // Si hay usuario, añadimos sus tipos específicos (ej. 'member').
        $allowedPriceTypes = ['regular', 'offer', 'liquidation']; 
        
        if ($user) {
            $allowedPriceTypes[] = 'member'; 
            // Aquí podrías añadir lógica para 'staff' si el usuario tiene ese rol
        }

        // 3. Construir la Consulta
        return Sku::query()
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            
            ->addSelect([
                'available_stock' => InventoryLot::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $branchId)
                    ->where('is_safety_stock', false) // <--- CORTE QUIRÚRGICO AQUÍ
            ])
            
            // B. EAGER LOAD DE PRECIOS
            // Cargamos solo los precios válidos para esta sucursal y este usuario
            ->with(['currentPrices' => function ($q) use ($branchId, $allowedPriceTypes) {
                $q->where('branch_id', $branchId)
                  ->whereIn('type', $allowedPriceTypes);
            }])
            
            // C. EAGER LOAD DE RELACIONES NECESARIAS
            ->with([
                'product.category.parent' // <--- AGREGAR ESTO para poder agrupar por pasillo
            ])
            
            ->get()
            
            // D. TRANSFORMACIÓN EN MEMORIA
            ->map(function ($sku) {
                // Determinar estado visual
                $sku->stock_status = $sku->available_stock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Calculamos el precio unitario usando el Accessor del Modelo Sku
                // (El modelo usará la relación 'currentPrices' que acabamos de inyectar)
                $sku->current_unit_price = $sku->display_price; 
                
                return $sku;
            });
    }
}