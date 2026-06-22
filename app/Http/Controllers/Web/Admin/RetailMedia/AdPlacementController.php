<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Models\RetailMedia\AdPlacement;
use App\Http\Resources\Admin\RetailMedia\AdPlacementResource;
use App\Http\Requests\Admin\RetailMedia\Placement\StorePlacementRequest;
use App\Http\Requests\Admin\RetailMedia\Placement\UpdatePlacementRequest;
use App\DTOs\Admin\RetailMedia\PlacementData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdPlacementController extends Controller
{
    public function index(): Response
    {
        $placements = AdPlacement::orderBy('code', 'asc')->get();

        return Inertia::render('Admin/RetailMedia/Placements/Index', [
            'placements' => AdPlacementResource::collection($placements)->resolve()
        ]);
    }

    public function store(StorePlacementRequest $request): RedirectResponse
    {
        $data = PlacementData::fromRequest($request);

        AdPlacement::create([
            'name'      => $data->name,
            'code'      => $data->code,
            'max_items' => $data->max_items,
            'is_active' => $data->is_active
        ]);

        return redirect()->route('admin.retail-media.ad-placements.index')
            ->with('success', 'MONETIZACIÓN: Espacio físico/virtual de visualización fijado.');
    }

    public function update(UpdatePlacementRequest $request, AdPlacement $adPlacement): RedirectResponse
    {
        $data = PlacementData::fromRequest($request);

        $adPlacement->update([
            'name'      => $data->name,
            'code'      => $data->code,
            'max_items' => $data->max_items,
            'is_active' => $data->is_active
        ]);

        return redirect()->route('admin.retail-media.ad-placements.index')
            ->with('success', 'MONETIZACIÓN: Restricciones de espacio modificadas.');
    }

    public function destroy(AdPlacement $adPlacement): RedirectResponse
    {
        $adPlacement->delete();
        return redirect()->back()->with('success', 'MONETIZACIÓN: Espacio de renderizado eliminado.');
    }
}