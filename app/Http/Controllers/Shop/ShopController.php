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
             $dto = CatalogQueryDTO::fromRequest($request, $branchId);
             $paginator = $action->execute($dto);
             $productsResource = ShopProductResource::collection($paginator);
             $productsResource->collection->each(function ($r) use ($branchId) {
                $r->setContextBranch($branchId);
                $r->additional(['type' => 'product']); 
             });
        } 
        
        // --- MODO MAPA (CORREGIDO) ---
        else {
            $zones = MarketZone::orderBy('name')->get();

            $zonesData = $zones->map(function($zone) {
                
                // 1. Obtenemos SOLO Categorías Raíz
                // Usamos 'roots()' y filtramos por zona y actividad
                $rootCategories = Category::roots()
                    ->where('market_zone_id', $zone->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();

                if ($rootCategories->isEmpty()) return null;

                // 2. SIN APLANAR (Aquí está el cambio clave)
                // Al pasar $rootCategories directo, el Resource recibe la categoría PADRE.
                // Por lo tanto, mostrará el 'name' y el 'image_path' de la categoría padre.
                
                $aisles = ShopCategoryResource::collection($rootCategories)->resolve();

                if (empty($aisles)) return null;

                return [
                    'slug' => $zone->slug,
                    'svg_id' => $zone->svg_id,
                    'name' => $zone->name,
                    'color' => $zone->hex_color,
                    'aisles' => $aisles // Ahora contiene: [ { name: "Licores", image: "img_licores.jpg" }, ... ]
                ];
            })->filter()->keyBy('slug');
        }
        $bundles = \App\Models\Bundle::where('branch_id', $branchId)
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function($bundle) {
                return [
                    'id' => $bundle->id,
                    'name' => $bundle->name,
                    'image_url' => $bundle->image_path ? asset('storage/' . $bundle->image_path) : null,
                    'slug' => $bundle->slug,
                    'type' => 'bundle', // <--- MARCADOR CLAVE
                    'price_display' => $bundle->fixed_price ? 'Bs '.$bundle->fixed_price : 'Ver Precio'
                ];
            });

        return Inertia::render('Shop/Index', [
            'products' => $productsResource,
            'zonesData' => $zonesData,
            'filters' => $request->only(['search', 'category_id', 'in_stock', 'type']),
            'bundlesData' => $bundles,
            'context' => [
                'branch_id' => $branchId,
                'branch_name' => $branchName,
                
            ]
            
        ]);
    }
    public function showZone(Request $request, MarketZone $zone, ShopContextService $contextService)
    {
        $branchId = $contextService->getActiveBranchId();

        // 1. Obtener SOLO Categorías Padre (Raíces)
        $rootCategories = Category::where('market_zone_id', $zone->id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 2. Construir Jerarquía: Padre -> Subcategorías -> Productos
        $hierarchicalData = $rootCategories->map(function ($parent) use ($branchId) {
            
            // Buscar hijos activos
            $children = Category::where('parent_id', $parent->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // Si no tiene hijos, quizás el padre tiene productos directos (opcional, pero seguro)
            // Para este caso, asumimos que los productos están en las subcategorías.
            
            $subcategoriesData = $children->map(function ($child) use ($branchId) {
                
                $products = Product::query()
                    ->where('category_id', $child->id)
                    ->where('is_active', true)
                    ->with([
                        'brand',
                        'skus' => function($q) use ($branchId) { 
                            $q->where('is_active', true)
                            ->with(['inventoryLots' => fn($qLot) => $qLot->where('branch_id', $branchId)]);
                        }
                    ])
                    ->take(15)
                    ->get();

                if ($products->isEmpty()) return null;

                $resourceCollection = ShopProductResource::collection($products);
                $resourceCollection->collection->each(function ($r) use ($branchId) {
                    $r->setContextBranch($branchId);
                });

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'products' => $resourceCollection->resolve()
                ];
            })->filter()->values();

            if ($subcategoriesData->isEmpty()) return null;

            return [
                'id' => $parent->id,
                'name' => $parent->name,
                'subcategories' => $subcategoriesData
            ];

        })->filter()->values();

        return Inertia::render('Shop/ZoneProducts', [
            'zone' => $zone,
            'groupedCategories' => $hierarchicalData,
            'targetCategory' => $request->input('category'), // Pasamos el ID para el scroll
        ]);
    }
}