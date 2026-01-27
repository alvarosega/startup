<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Bundle; // <--- Importante
use App\Models\Branch;
use App\Models\InventoryLot; // <--- Importante para stock real
use App\Services\ShopContextService;
use App\DTOs\Shop\CatalogQueryDTO;
use App\Actions\Shop\GetShopCatalogAction;
use App\Http\Resources\Shop\ShopProductResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ShopController extends Controller
{
    public function index(
        Request $request, 
        ShopContextService $contextService, 
        GetShopCatalogAction $action
    ) {
        // 1. Obtener Contexto
        $branchId = $contextService->getActiveBranchId();
        $branchName = Branch::find($branchId)?->name ?? 'Desconocida';

        // 2. LÓGICA DE BUNDLES (PACKS)
        if ($request->input('type') === 'bundles') {
            
            // Query para Bundles Activos
            $query = Bundle::query()
                ->where('is_active', true)
                ->with(['skus.product', 'skus.prices']); // Cargar relaciones vitales

            // Filtro de búsqueda por nombre
            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            // Paginación
            $bundlesPaginated = $query->paginate(20)->withQueryString();

            // TRANSFORMACIÓN MANUAL (Para igualar la estructura de productos)
            $data = $bundlesPaginated->getCollection()->map(function ($bundle) use ($branchId) {
                
                // A. Calcular Precio Dinámico (Suma de los componentes en ESTA sucursal)
                $price = $bundle->skus->sum(function ($sku) use ($branchId) {
                    $unitPrice = $sku->getCurrentPrice($branchId);
                    return $unitPrice * $sku->pivot->quantity;
                });

                // B. Calcular Stock Dinámico (Lógica Cuello de Botella)
                // El stock del pack es igual al componente que menos alcance tiene.
                $maxStock = 999999;
                
                foreach ($bundle->skus as $sku) {
                    // Stock Real = Físico - Reservado
                    $realStockSku = InventoryLot::where('branch_id', $branchId)
                        ->where('sku_id', $sku->id)
                        ->sum(DB::raw('quantity - reserved_quantity'));
                    
                    $qtyNeeded = $sku->pivot->quantity;
                    
                    if ($qtyNeeded > 0) {
                        // Cuántos packs puedo armar con este SKU específico
                        $possibleBundles = floor($realStockSku / $qtyNeeded);
                        if ($possibleBundles < $maxStock) {
                            $maxStock = $possibleBundles;
                        }
                    }
                }
                
                // Si el bundle no tiene items, el stock es 0
                if ($bundle->skus->isEmpty()) $maxStock = 0;

                // Estructura idéntica a ShopProductResource para que Vue la lea bien
                return [
                    'id' => $bundle->id,
                    'slug' => $bundle->slug,
                    'name' => $bundle->name,
                    'brand' => 'PACK OFICIAL',
                    'image_url' => $bundle->image_path ?? '/images/bundle-placeholder.png',
                    'price_display' => number_format($price, 2),
                    'is_available' => true,
                    'has_stock' => $maxStock > 0,
                    'type' => 'bundle', // <--- CLAVE: Esto le dice a Vue que abra el Modal de Packs
                    'variants' => [] // Array vacío para no romper validaciones de Vue
                ];
            });

            // Reemplazar la colección original con la transformada
            $bundlesPaginated->setCollection($data);
            $productsResource = $bundlesPaginated;

        } else {
            // 3. LÓGICA DE PRODUCTOS (Tu código original)
            $dto = CatalogQueryDTO::fromRequest($request, $branchId);
            $paginator = $action->execute($dto);

            $productsResource = ShopProductResource::collection($paginator);

            // Inyectamos contexto y forzamos el tipo
            $productsResource->collection->each(function ($resource) use ($branchId) {
                $resource->setContextBranch($branchId);
                $resource->additional(['type' => 'product']); 
            });
        }

        return Inertia::render('Shop/Index', [
            'products' => $productsResource,
            'filters' => $request->only(['search', 'category_id', 'in_stock', 'type']),
            'context' => [
                'branch_id' => $branchId,
                'branch_name' => $branchName,
                'is_fallback' => $branchId === 1 && !session('shop_address_id')
            ]
        ]);
    }
}