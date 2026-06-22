<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\RetailMedia\AdCampaign;
use App\Models\Operations\Provider;
use App\Http\Resources\Admin\RetailMedia\AdCampaignResource;
use App\Http\Requests\Admin\RetailMedia\Campaign\StoreCampaignRequest;
use App\Http\Requests\Admin\RetailMedia\Campaign\UpdateCampaignRequest;
use App\DTOs\Admin\RetailMedia\CampaignData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdCampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = AdCampaign::with('provider')->orderBy('created_at', 'desc')->get();
        $providers = Provider::where('deleted_epoch', 0)->get(['id', 'company_name']);

        return Inertia::render('Admin/RetailMedia/Campaigns/Index', [
            'campaigns' => AdCampaignResource::collection($campaigns)->resolve(),
            'providers' => $providers->map(fn($p) => ['id' => $p->id, 'name' => mb_toUpperCase($p->company_name)])
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $data = CampaignData::fromRequest($request);

        AdCampaign::create([
            'provider_id' => $data->provider_id,
            'name'        => $data->name,
            'type'        => $data->type,
            'starts_at'   => $data->starts_at,
            'ends_at'     => $data->ends_at,
            'is_active'   => $data->is_active
        ]);

        return redirect()->route('admin.retail-media.ad-campaigns.index')
            ->with('success', 'MONETIZACIÓN: Contenedor de campaña publicitaria registrado.');
    }

    public function update(UpdateCampaignRequest $request, AdCampaign $adCampaign): RedirectResponse
    {
        $data = CampaignData::fromRequest($request);

        $adCampaign->update([
            'provider_id' => $data->provider_id,
            'name'        => $data->name,
            'type'        => $data->type,
            'starts_at'   => $data->starts_at,
            'ends_at'     => $data->ends_at,
            'is_active'   => $data->is_active
        ]);

        return redirect()->route('admin.retail-media.ad-campaigns.index')
            ->with('success', 'MONETIZACIÓN: Parámetros de campaña modificados.');
    }

    public function destroy(AdCampaign $adCampaign): RedirectResponse
    {
        $adCampaign->delete();
        return redirect()->back()->with('success', 'MONETIZACIÓN: Campaña eliminada.');
    }
}