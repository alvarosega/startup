<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RetailMedia\StoreCreativeRequest;
use App\DTOs\Admin\RetailMedia\CreativeData;
use App\Http\Resources\Admin\RetailMedia\CreativeResource;
use App\Actions\Admin\RetailMedia\{StoreCreativeAction, UpdateCreativeAction};
use App\Models\{AdCreative, AdCampaign, AdPlacement, Branch, Sku, Category, Bundle, Brand};
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdCreativeController extends Controller
{
    public function index(): Response
    {
        $creatives = AdCreative::with(['campaign', 'placement', 'branch', 'target', 'sku', 'category', 'bundle', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/RetailMedia/Creatives/Index', [
            'creatives'  => CreativeResource::collection($creatives)->resolve(),
            'campaigns'  => AdCampaign::select('id', 'name')->where('is_active', true)->get(),
            'placements' => AdPlacement::select('id', 'name', 'code')->where('is_active', true)->get(),
            'branches'   => Branch::select('id', 'name')->get(),
            'categories' => Category::select('id', 'name')->get(),
            'bundles'    => Bundle::select('id', 'name')->where('is_active', true)->get(),
            'brands'     => Brand::select('id', 'name')->get(),
        ]);
    }

    public function store(StoreCreativeRequest $request, StoreCreativeAction $action): RedirectResponse
    {
        $action->execute(CreativeData::fromRequest($request->validated()));
        return back()->with('toast', ['type' => 'success', 'message' => 'Pieza publicitaria inyectada de forma correcta.']);
    }

    public function update(StoreCreativeRequest $request, string $id, UpdateCreativeAction $action): RedirectResponse
    {
        $creative = AdCreative::findOrFail($id);
        $action->execute($creative, CreativeData::fromRequest($request->validated()));
        return back()->with('toast', ['type' => 'success', 'message' => 'Contenido y mapeo de la pieza actualizados.']);
    }

    public function destroy(string $id): RedirectResponse
    {
        $creative = AdCreative::findOrFail($id);
        if ($creative->image_mobile_path) { \Illuminate\Support\Facades\Storage::disk('public')->delete($creative->image_mobile_path); }
        if ($creative->image_desktop_path) { \Illuminate\Support\Facades\Storage::disk('public')->delete($creative->image_desktop_path); }
        $creative->delete();
        return back()->with('toast', ['type' => 'info', 'message' => 'Pieza publicitaria eliminada físicamente.']);
    }
}