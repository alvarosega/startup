<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\{AdCreative, AdCampaign, AdPlacement, Branch, Brand, Category};
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
    public function index(Request $request, ListAdCreativesAction $action): Response 
    {
        $dto = AdCreativeFilterDTO::fromRequest($request);
        $results = $action->execute($dto);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Index', [
            'items' => AdCreativeResource::collection($results),
            'filters' => $request->only(['placement_code', 'branch_id', 'is_active']),
            'placements' => AdPlacement::where('is_active', true)->get(['code', 'name']),
            'branches' => Branch::active()->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'campaigns'  => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'code', 'name']),
            'branches'   => Branch::active()->get(['id', 'name']),
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function store(UpsertAdCreativeRequest $request, UpsertAdCreativeAction $action): RedirectResponse 
    {
        $action->execute(UpsertAdCreativeDTO::fromRequest($request));
        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Activo publicitario desplegado.');
    }

    public function edit(string $id): Response
    {
        $creative = AdCreative::with([
            'campaign', 'placement', 'branch', 'category', 'brand', 'sku.product', 'bundle'
        ])->findOrFail($id);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'creative'   => new AdCreativeResource($creative),
            'campaigns'  => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'name', 'code']),
            'branches'   => Branch::active()->get(['id', 'name']),
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function update(UpsertAdCreativeRequest $request, UpsertAdCreativeAction $action, string $id): RedirectResponse 
    {
        $action->execute(UpsertAdCreativeDTO::fromRequest($request));
        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Sincronización de activo completada.');
    }

    public function destroy(string $id, DeleteAdCreativeAction $action): RedirectResponse
    {
        $action->execute($id);
        return redirect()->route('admin.retail-media.ad-creatives.index');
    }

    // --- MOTORES DE BÚSQUEDA AJAX ---

    public function searchSkus(Request $request, SearchSkusAction $action): JsonResponse
    {
        return response()->json($action->execute($request->query('q', '')));
    }

    public function searchBundles(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $results = \App\Models\Bundle::where('name', 'like', "%{$q}%")
            ->active()->limit(10)->get()
            ->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'code' => strtoupper($b->type)]);
            
        return response()->json($results);
    }

    public function searchBrands(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $results = Brand::where('name', 'like', "%{$q}%")
            ->active()->limit(10)->get()
            ->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'code' => $b->slug]);

        return response()->json($results);
    }
}