<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\MarketZone;
use App\Models\Category;
use App\Models\Product;
use App\Services\ShopContextService;
use App\DTOs\Shop\CatalogQueryDTO;
use App\Actions\Shop\GetShopCatalogAction;
use App\Http\Resources\Shop\ShopProductResource;
// IMPORTAMOS EL NUEVO RECURSO
use App\Http\Resources\Shop\ShopCategoryResource; 

class ShopController extends Controller
{
    public function index(
        Request $request, 
        ShopContextService $contextService, 
        GetShopCatalogAction $action
    ) {
        $branchId = $contextService->getActiveBranchId();
        $branchName = Branch::find($branchId)?->name ?? 'Desconocida';

        $productsResource = null;
        $zonesData = null;

        // --- MODO BÚSQUEDA ---
        if ($request->filled('search') || $request->filled('category_id') || $request->input('type') === 'bundles') {
             // ... tu lógica de filtros (sin cambios) ...
             $dto = CatalogQueryDTO::fromRequest($request, $branchId);
             $paginator = $action->execute($dto);
             $productsResource = ShopProductResource::collection($paginator);
             $productsResource->collection->each(function ($r) use ($branchId) {
                $r->setContextBranch($branchId);
                $r->additional(['type' => 'product']); 
             });
        } 
        
        // --- MODO MAPA (SOLUCIÓN AQUÍ) ---
        else {
            $zones = MarketZone::orderBy('name')->get();

            $zonesData = $zones->map(function($zone) use ($branchId) {
                
                // 1. Obtenemos Árbol de Categorías
                $rootCategories = Category::roots()
                    ->where('market_zone_id', $zone->id)
                    ->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true)->orderBy('sort_order');
                    }])
                    ->orderBy('sort_order')
                    ->get();

                if ($rootCategories->isEmpty()) return null;

                // 2. Aplanamos (Raíz -> Subcategorías)
                $aislesRaw = $rootCategories->flatMap(function($root) {
                    return $root->children->isNotEmpty() ? $root->children : collect([$root]);
                });

                // 3. TRANSFORMACIÓN CON RESOURCE (Aquí es donde se arregla la imagen)
                // Usamos el Resource para serializar correctamente los modelos
                $aisles = ShopCategoryResource::collection($aislesRaw)->resolve();

                // 4. (Opcional) Filtrar categorías vacías de stock
                /*
                $aisles = collect($aisles)->filter(function($aisle) use ($branchId) {
                     return Product::where('category_id', $aisle['id'])
                        ->where('is_active', true)
                        ->whereHas('inventoryLots', fn($q) => $q->where('branch_id', $branchId)->where('quantity', '>', 0))
                        ->exists();
                })->values();
                */

                if (empty($aisles)) return null;

                return [
                    'slug' => $zone->slug,
                    'svg_id' => $zone->svg_id,
                    'name' => $zone->name,
                    'color' => $zone->hex_color,
                    'aisles' => $aisles
                ];
            })->filter()->keyBy('slug');
        }

        return Inertia::render('Shop/Index', [
            'products' => $productsResource,
            'zonesData' => $zonesData,
            'filters' => $request->only(['search', 'category_id', 'in_stock', 'type']),
            'context' => [
                'branch_id' => $branchId,
                'branch_name' => $branchName,
            ]
        ]);
    }
    public function showZone(Request $request, MarketZone $zone, ShopContextService $contextService)
    {
        $branchId = $contextService->getActiveBranchId();

        // 1. Obtener todas las categorías de esta zona (Padres e Hijas)
        $rootCategories = Category::where('market_zone_id', $zone->id)->get();
        // Buscamos también las subcategorías de esas raíces
        $childCategories = Category::whereIn('parent_id', $rootCategories->pluck('id'))->get();
        
        // Unimos todas para procesarlas
        $allCategories = $rootCategories->merge($childCategories)->unique('id');

        // 2. Construir la estructura "Netflix" (Fila por Categoría)
        $groupedData = $allCategories->map(function ($category) use ($branchId) {
            
            // Consulta DIRECTA a productos (Evita el error de relación en Category)
            $products = Product::query()
                ->select('products.*') // Seleccionamos solo datos del producto para evitar colisiones
                ->join('skus', 'products.id', '=', 'skus.product_id')
                ->join('inventory_lots', 'skus.id', '=', 'inventory_lots.sku_id')
                ->where('products.category_id', $category->id)
                ->where('products.is_active', true)
                ->where('inventory_lots.branch_id', $branchId)
                ->where('inventory_lots.quantity', '>', 0)
                ->with(['brand']) // Eager Loading
                ->distinct() // Evitar duplicados si un producto tiene varios lotes
                ->take(15)
                ->get();

            if ($products->isEmpty()) return null;

            // Transformamos los productos para que tengan precio e imagen correctos
            $productsResource = ShopProductResource::collection($products);
            $productsResource->collection->each(function ($r) use ($branchId) {
                $r->setContextBranch($branchId);
            });

            return [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $productsResource
            ];
        })->filter()->values(); // Eliminamos las categorías vacías

        return Inertia::render('Shop/ZoneProducts', [
            'zone' => $zone,
            'groupedCategories' => $groupedData, // Enviamos los datos listos
        ]);
    }
}