<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Bundle;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Bundle\BundleItem;
use App\Models\Catalog\Sku;
use App\Http\Resources\Admin\Bundle\BundleResource;
use App\Http\Resources\Admin\Bundle\BundleItemResource;
use App\Http\Requests\Admin\Bundle\StoreBundleRequest;
use App\Http\Requests\Admin\Bundle\UpdateBundleRequest;
use App\DTOs\Admin\Bundle\BundleData;
use App\Actions\Admin\Bundle\StoreBundle;
use App\Actions\Admin\Bundle\UpdateBundle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

final class BundleController extends Controller
{
    public function index(): Response
    {
        $bundles = Bundle::orderBy('created_at', 'desc')->get();
        $skus = Sku::whereNull('deleted_at')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => BundleResource::collection($bundles)->resolve(),
            'skus'    => $skus->map(fn($s) => ['id' => $s->id, 'name' => mb_toUpperCase($s->name), 'code' => $s->code])
        ]);
    }

    public function store(StoreBundleRequest $request, StoreBundle $action): RedirectResponse
    {
        $action->execute(BundleData::fromRequest($request));

        return redirect()->route('admin.bundles.index')
            ->with('success', 'SINO_COMBO: Agrupación comercial estructurada y disponible.');
    }

    public function update(UpdateBundleRequest $request, Bundle $bundle, UpdateBundle $action): RedirectResponse
    {
        $action->execute($bundle, BundleData::fromRequest($request));

        return redirect()->route('admin.bundles.index')
            ->with('success', 'SINO_COMBO: Parámetros del combo actualizados.');
    }

    /**
     * Resolución de componentes bajo demanda (Carga diferida síncrona para optimización de UI)
     */
    public function items(string $id): JsonResponse
    {
        $items = BundleItem::with('sku')->where('bundle_id', $id)->get();

        return response()->json([
            'items' => BundleItemResource::collection($items)->resolve()
        ]);
    }

    public function destroy(Bundle $bundle): RedirectResponse
    {
        $bundle->delete();

        return redirect()->route('admin.bundles.index')
            ->with('success', 'SINO_COMBO: Registro de combo removido de la suite administrativa.');
    }
}