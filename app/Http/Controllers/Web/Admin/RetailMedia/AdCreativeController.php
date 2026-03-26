<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\{AdCreative, AdCampaign, AdPlacement, Branch, Sku, Bundle, Category};
use App\DTOs\Admin\RetailMedia\{AdCreativeFilterDTO, UpsertAdCreativeDTO};
use App\Http\Requests\Admin\RetailMedia\UpsertAdCreativeRequest;
use App\Actions\Admin\RetailMedia\{
    ListAdCreativesAction, 
    UpsertAdCreativeAction, 
    SearchSkusAction, 
    DeleteAdCreativeAction
};
use App\Http\Resources\Admin\RetailMedia\AdCreativeResource;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Inertia\{Inertia, Response};

class AdCreativeController extends Controller
{
    /**
     * Listado maestro de creativos con filtrado táctico.
     */
    public function index(
        Request $request, 
        ListAdCreativesAction $action
    ): Response {
        $dto = AdCreativeFilterDTO::fromRequest($request);
        $results = $action->execute($dto);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Index', [
            'items' => AdCreativeResource::collection($results),
            'filters' => $request->only(['placement_code', 'branch_id', 'is_active']),
            'placements' => AdPlacement::where('is_active', true)->get(['code', 'name']), // <--- Añadir
            'branches' => Branch::active()->get(['id', 'name']),                         // <--- Añadir
        ]);
    }

    /**
     * Formulario de creación: Inyecta dependencias de anclaje y target.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'campaigns'  => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['code', 'name']),
            'branches'   => Branch::active()->get(['id', 'name']),
            'categories' => Category::active()->get(['id', 'name']), // Necesario para banners por pasillo
        ]);
    }

    /**
     * Persistencia de nuevo creativo (Soporta archivos Mobile/Desktop).
     */
    public function store(
        UpsertAdCreativeRequest $request,
        UpsertAdCreativeAction $action
    ): RedirectResponse {
        $dto = UpsertAdCreativeDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo publicitario desplegado exitosamente.');
    }

    /**
     * Formulario de edición: Carga relaciones de categorías y sucursales.
     */
    public function edit(string $id): Response
    {
        $creative = AdCreative::with([
            'campaign', 
            'placement', 
            'branch', 
            'category', 
            'sku.product', 
            'bundle'
        ])->findOrFail($id);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'creative'   => new AdCreativeResource($creative),
            'campaigns'  => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'name', 'code']),
            'branches'   => Branch::active()->get(['id', 'name']),
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    /**
     * Actualización de creativo existente.
     */
    public function update(
        UpsertAdCreativeRequest $request,
        UpsertAdCreativeAction $action,
        string $id
    ): RedirectResponse {
        // El DTO captura el ID desde la ruta automáticamente si está configurado, 
        // o lo pasamos manualmente.
        $dto = UpsertAdCreativeDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo actualizado y sincronizado.');
    }

    /**
     * Eliminación física y de archivos.
     */
    public function destroy(string $id, DeleteAdCreativeAction $action): RedirectResponse
    {
        $action->execute($id);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo eliminado del sistema.');
    }

    // =========================================================================
    // MOTORES DE BÚSQUEDA AJAX (Utilizados por el Formulario Vue)
    // =========================================================================

    /**
     * Buscador de SKUs para el destino del banner.
     */
    public function searchSkus(Request $request, SearchSkusAction $action): JsonResponse
    {
        $term = $request->query('q', '');
        $results = $action->execute($term);
    
        return response()->json($results);
    }

    /**
     * Buscador de Bundles (Packs) para el destino del banner.
     */
    public function searchBundles(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $bundles = Bundle::where('name', 'like', "%{$q}%")
            ->active()
            ->limit(10)
            ->get()
            ->map(fn($b) => [
                'id'   => $b->id,
                'name' => $b->name,
                'code' => strtoupper($b->type) . ": " . $b->slug
            ]);

        return response()->json($bundles);
    }
}