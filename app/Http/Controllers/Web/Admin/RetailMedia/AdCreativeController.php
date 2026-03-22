<?php

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\DTOs\Admin\RetailMedia\AdCreativeFilterDTO;
use App\Actions\Admin\RetailMedia\ListAdCreativesAction;
use App\Http\Resources\Admin\RetailMedia\AdCreativeResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\AdCampaign;
use App\Models\AdPlacement;
use App\Models\Branch;
use App\Models\Sku;
use App\DTOs\Admin\RetailMedia\UpsertAdCreativeDTO;
use App\Http\Requests\Admin\RetailMedia\UpsertAdCreativeRequest;
use App\Actions\Admin\RetailMedia\UpsertAdCreativeAction;
use Illuminate\Http\RedirectResponse;
use App\Actions\Admin\RetailMedia\SearchSkusAction;
use Illuminate\Http\JsonResponse;
use App\Actions\Admin\RetailMedia\DeleteAdCreativeAction;

class AdCreativeController extends Controller
{
    public function index(
        Request $request, 
        ListAdCreativesAction $action
    ): Response {
        $dto = AdCreativeFilterDTO::fromRequest($request);
        $results = $action->execute($dto);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Index', [
            'items' => AdCreativeResource::collection($results),
            'filters' => $request->only(['placement_code', 'market_zone_id', 'branch_id', 'is_active'])
        ]);
    }
    public function create(): Response
    {
        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'campaigns' => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'name', 'code']),
            'branches' => Branch::active()->get(['id', 'name']),
            'initial_skus' => Sku::where('is_active', true)->limit(10)->get(['id', 'name']), // Para el select inicial
        ]);
    }

    public function store(
        UpsertAdCreativeRequest $request,
        UpsertAdCreativeAction $action
    ): RedirectResponse {
        $dto = UpsertAdCreativeDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo publicitario creado exitosamente.');
    }
    public function searchSkus(Request $request, SearchSkusAction $action): \Illuminate\Http\JsonResponse
    {
        // 1. Verifica si llega el término: dd($request->query('q'));
        $term = $request->query('q');
        
        // 2. Ejecuta la lógica
        $results = $action->execute($term);
    
        // 3. Retorna JSON puro
        return response()->json($results);
    }
    public function edit(string $id): Response
    {
        $creative = AdCreative::with(['branches:id', 'sku:id,name'])->findOrFail($id);

        return Inertia::render('Admin/RetailMedia/AdCreatives/Form', [
            'creative' => new AdCreativeResource($creative),
            'campaigns' => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'name', 'code']),
            'branches' => Branch::active()->get(['id', 'name']),
        ]);
    }

    public function update(
        UpsertAdCreativeRequest $request,
        UpsertAdCreativeAction $action,
        string $id
    ): RedirectResponse {
        $dto = UpsertAdCreativeDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo actualizado correctamente.');
    }

    public function destroy(string $id, DeleteAdCreativeAction $action): RedirectResponse
    {
        $action->execute($id);

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'Creativo y sus archivos eliminados.');
    }
}