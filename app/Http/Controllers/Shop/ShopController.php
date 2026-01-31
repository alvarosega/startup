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

        // 1. Obtener categorías (Igual que antes)
        $rootCategories = Category::where('market_zone_id', $zone->id)->get();
        $childCategories = Category::whereIn('parent_id', $rootCategories->pluck('id'))->get();
        $allCategories = $rootCategories->merge($childCategories)->unique('id');

        // 2. Agrupar Productos
        $groupedData = $allCategories->map(function ($category) use ($branchId) {
                    
            $products = Product::query()
                ->where('category_id', $category->id)
                ->where('is_active', true)
                // CARGA CORRECTA DE RELACIONES
                ->with([
                    'brand', // <--- IMPORTANTE: Cargar la marca para que no salga "Genérico"
                    'skus' => function($q) use ($branchId) { 
                        $q->where('is_active', true)
                        ->with([
                                'inventoryLots' => fn($qLot) => $qLot->where('branch_id', $branchId)
                        ]);
                    }
                ])
                ->take(15) // Traer máximo 15 por carrusel
                ->get();

            // Si la categoría no tiene productos, la saltamos
            if ($products->isEmpty()) return null;

            // --- CORRECCIÓN CRÍTICA AQUÍ ---
            $resourceCollection = ShopProductResource::collection($products);
            
            // Inyectar la sucursal
            $resourceCollection->collection->each(function ($r) use ($branchId) {
                $r->setContextBranch($branchId);
            });

            return [
                'id' => $category->id,
                'name' => $category->name,
                // USAMOS ->resolve() PARA ENVIAR UN ARRAY PURO A VUE, NO UN OBJETO {data:...}
                'products' => $resourceCollection->resolve() 
            ];

        })->filter()->values();

        return Inertia::render('Shop/ZoneProducts', [
            'zone' => $zone,
            'groupedCategories' => $groupedData,
        ]);
    }
}