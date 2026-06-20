<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Bundle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bundle\StoreBundleRequest;
use App\DTOs\Admin\Bundle\BundleData;
use App\Http\Resources\Admin\Bundle\BundleResource;
use App\Actions\Admin\Bundle\{StoreBundleAction, UpdateBundleAction, DestroyBundleAction};
use App\Models\Bundle;
use App\Models\Sku;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BundleController extends Controller
{
    public function index(): Response
    {
        $bundles = Bundle::with(['skus:id,name,code'])
            ->orderBy('created_at', 'desc')
            ->get();

        $availableSkus = Sku::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Admin/Bundle/Index', [
            'bundles'       => BundleResource::collection($bundles)->resolve(),
            'availableSkus' => $availableSkus
        ]);
    }

    public function store(StoreBundleRequest $request, StoreBundleAction $action): RedirectResponse
    {
        $dto = BundleData::fromRequest($request->validated());
        $action->execute($dto);

        return redirect()->route('bundles.index')->with('toast', [
            'type'    => 'success',
            'message' => 'Grupo comercial / plantilla estructurada con éxito.'
        ]);
    }

    public function update(StoreBundleRequest $request, string $id, UpdateBundleAction $action): RedirectResponse
    {
        $bundle = Bundle::findOrFail($id);
        $dto = BundleData::fromRequest($request->validated());
        $action->execute($bundle, $dto);

        return redirect()->route('bundles.index')->with('toast', [
            'type'    => 'success',
            'message' => 'Estructura de campaña actualizada correctamente.'
        ]);
    }

    public function destroy(string $id, DestroyBundleAction $action): RedirectResponse
    {
        $bundle = Bundle::findOrFail($id);
        $action->execute($bundle);

        return redirect()->route('bundles.index')->with('toast', [
            'type'    => 'info',
            'message' => 'Grupo comercial eliminado de los registros.'
        ]);
    }
}