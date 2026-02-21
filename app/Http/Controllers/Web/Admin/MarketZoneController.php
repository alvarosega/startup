<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketZone;
use App\Models\Category;
use App\Http\Requests\Admin\Market\MarketZoneRequest;
use App\DTOs\Admin\MarketZone\MarketZoneDTO;
use App\Actions\Admin\MarketZone\UpsertMarketZoneAction;
use App\Http\Resources\Admin\MarketZone\MarketZoneResource;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class MarketZoneController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', MarketZone::class);
        
        // Uso de lógica encapsulada en el modelo (Clean Code)
        $zones = MarketZone::getAllWithStats();

        return Inertia::render('Admin/MarketZones/Index', [
            'zones' => MarketZoneResource::collection($zones)->resolve()
        ]);
    }

    public function store(MarketZoneRequest $request, UpsertMarketZoneAction $action)
    {
        $this->authorize('create', MarketZone::class);
        $action->execute(MarketZoneDTO::fromRequest($request));
    
        return redirect()->route('admin.market-zones.index')->with('success', 'Zona operativa.');
    }

    

    public function create()
    {
        $this->authorize('create', MarketZone::class);

        return Inertia::render('Admin/MarketZones/Create', [
            // MODIFICADO: Uso de método estático del modelo
            'available_categories' => Category::getRootsForZoneAssignment()
        ]);
    }

    public function edit(MarketZone $marketZone)
    {
        $this->authorize('update', $marketZone);

        // 1. Cargar relación para el Resource
        $marketZone->load('categories:id,name,market_zone_id');

        return Inertia::render('Admin/MarketZones/Edit', [
            // MODIFICADO: Uso de Resource para consistencia
            'zone' => (new MarketZoneResource($marketZone))->resolve(),
            // MODIFICADO: Uso de método estático del modelo
            'available_categories' => Category::getRootsForZoneAssignment()
        ]);
    }

    public function update(MarketZoneRequest $request, MarketZone $marketZone, UpsertMarketZoneAction $action)
    {
        $dto = MarketZoneDTO::fromRequest($request);
        $action->execute($dto, $marketZone);

        return redirect()->route('admin.market-zones.index')
            ->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(MarketZone $marketZone)
    {
        if ($marketZone->categories()->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar la zona porque tiene categorías asignadas.'
            ]);
        }

        $marketZone->delete();

        return redirect()->route('admin.market-zones.index')
            ->with('success', 'Zona eliminada.');
    }
}