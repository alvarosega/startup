<?php

namespace App\Http\Controllers\Web\Admin\MarketZone;

use App\Http\Controllers\Controller;
use App\Models\MarketZone;
use App\DTOs\Admin\MarketZone\MarketZoneData;
use App\Http\Requests\Admin\MarketZone\StoreMarketZoneRequest;
use App\Actions\Admin\MarketZone\{UpsertMarketZoneAction, DeleteMarketZoneAction};
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\Admin\MarketZone\MarketZoneResource;
use Illuminate\Support\Facades\Cache;

class MarketZoneController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MarketZone::class);

        // CORRECCIÓN: Cache sin tags para compatibilidad universal temporal.
        // Cachea por 24h (86400 segs).
        $zones = Cache::remember('market_zones_admin_list', 86400, function () {
            return MarketZone::withCount('brands')->orderBy('name')->get();
        });

        return Inertia::render('Admin/MarketZones/Index', [
            'zones' => MarketZoneResource::collection($zones)
        ]);
    }
    public function store(StoreMarketZoneRequest $request, UpsertMarketZoneAction $action): RedirectResponse
    {
        $this->authorize('create', MarketZone::class);
        
        $action->execute(MarketZoneData::fromRequest($request));

        return redirect()->route('admin.market-zones.index')->with('success', 'Zona de mercado operativa.');
    }

    public function update(StoreMarketZoneRequest $request, MarketZone $marketZone, UpsertMarketZoneAction $action): RedirectResponse
    {
        $this->authorize('update', $marketZone);
        
        $action->execute(MarketZoneData::fromRequest($request), $marketZone);

        return redirect()->route('admin.market-zones.index')->with('success', 'Zona de mercado reconfigurada.');
    }

    public function destroy(MarketZone $marketZone, DeleteMarketZoneAction $action): RedirectResponse
    {
        $this->authorize('delete', $marketZone);

        $action->execute($marketZone);

        return redirect()->route('admin.market-zones.index')->with('success', 'Zona de mercado neutralizada.');
    }
}