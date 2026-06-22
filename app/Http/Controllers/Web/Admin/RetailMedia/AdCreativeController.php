<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\RetailMedia\AdCreative;
use App\Models\RetailMedia\AdCampaign;
use App\Models\RetailMedia\AdPlacement;
use App\Models\Operations\Branch;
use App\Models\Bundle\Bundle;
use App\Models\Catalog\Sku;
use App\Models\Catalog\Category;
use App\Models\Catalog\Brand;
use App\Http\Resources\Admin\RetailMedia\AdCreativeResource;
use App\Http\Requests\Admin\RetailMedia\Creative\StoreCreativeRequest;
use App\Http\Requests\Admin\RetailMedia\Creative\UpdateCreativeRequest;
use App\DTOs\Admin\RetailMedia\CreativeData;
use App\Actions\Admin\RetailMedia\StoreCreative;
use App\Actions\Admin\RetailMedia\UpdateCreative;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdCreativeController extends Controller
{
    public function index(): Response
    {
        $creatives = AdCreative::with(['campaign', 'placement', 'branch', 'sku', 'category', 'brand', 'bundle'])
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Admin/RetailMedia/Creatives/Index', [
            'creatives'  => AdCreativeResource::collection($creatives)->resolve(),
            'campaigns'  => AdCampaign::where('is_active', true)->get(['id', 'name']),
            'placements' => AdPlacement::where('is_active', true)->get(['id', 'name', 'code']),
            'branches'   => Branch::where('deleted_epoch', 0)->get(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'brands'     => Brand::all(['id', 'name']),
            'bundles'    => Bundle::where('is_active', true)->get(['id', 'name']),
            'skus'       => Sku::whereNull('deleted_at')->get(['id', 'name', 'code'])
        ]);
    }

    public function store(StoreCreativeRequest $request, StoreCreative $action): RedirectResponse
    {
        $action->execute(CreativeData::fromRequest($request));

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'MONETIZACIÓN: Banner publicitario integrado e indexado al árbol estructural.');
    }

    public function update(UpdateCreativeRequest $request, AdCreative $adCreative, UpdateCreative $action): RedirectResponse
    {
        $action->execute($adCreative, CreativeData::fromRequest($request));

        return redirect()->route('admin.retail-media.ad-creatives.index')
            ->with('success', 'MONETIZACIÓN: Archivos y enlaces del banner sincronizados.');
    }

    public function destroy(AdCreative $adCreative): RedirectResponse
    {
        $adCreative->delete();
        return redirect()->back()->with('success', 'MONETIZACIÓN: Banner removido del servidor.');
    }
}