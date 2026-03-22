<?php

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\Provider;
use App\Models\MarketZone;
use App\DTOs\Admin\RetailMedia\AdCampaignDTO;
use App\Actions\Admin\RetailMedia\UpsertAdCampaignAction;
use App\Http\Resources\Admin\RetailMedia\AdCampaignResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AdCampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = AdCampaign::with(['provider', 'marketZone'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/RetailMedia/AdCampaigns/Index', [
            'items' => AdCampaignResource::collection($campaigns)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RetailMedia/AdCampaigns/Form', [
            'providers' => Provider::active()->get(['id', 'commercial_name']),
            'market_zones' => MarketZone::active()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, UpsertAdCampaignAction $action): RedirectResponse
    {
        // Validación rápida inline para mantener el flujo
        $request->validate([
            'name' => 'required|string|max:255',
            'provider_id' => 'required|uuid|exists:providers,id',
            'type' => 'required|in:PAID,INTERNAL',
        ]);

        $dto = AdCampaignDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.retail-media.ad-campaigns.index')
            ->with('success', 'Campaña comercial registrada.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $campaign = AdCampaign::findOrFail($id);
        
        // Regla Zero-Trust: No borrar si tiene banners activos
        if ($campaign->creatives()->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar una campaña con banners vinculados.']);
        }

        $campaign->delete();
        return redirect()->route('admin.retail-media.ad-campaigns.index');
    }
}