<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Bundle, Sku, Branch};
use App\Http\Requests\Admin\Bundle\BundleRequest;
use App\DTOs\Admin\Bundle\BundleDTO;
use App\Actions\Admin\Bundle\{UpsertBundleAction, DeleteBundleAction};
use App\Http\Resources\Admin\Bundle\BundleResource;
use App\Http\Resources\Admin\Sku\SkuSelectResource; // Namespace corregido
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class BundleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Bundle::class);

        // La lógica de filtrado se movió al Modelo (Encapsulación)
        $bundles = Bundle::getPaginatedForAdmin($request->only(['search', 'branch_id']));

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => BundleResource::collection($bundles),
            'branches' => Branch::getMinimalList() // Método estático selectivo
        ]);
    }

    public function create()
    {
        $this->authorize('create', Bundle::class);

        return Inertia::render('Admin/Bundles/Create', [
            'skus' => SkuSelectResource::collection(Sku::getAvailableForBundles()),
            'branches' => Branch::getMinimalList()
        ]);
    }

    public function store(BundleRequest $request, UpsertBundleAction $action)
    {
        $this->authorize('create', Bundle::class);
        
        $action->execute(BundleDTO::fromRequest($request));

        return redirect()->route('admin.bundles.index')->with('success', 'Pack operativo.');
    }

    public function edit(Bundle $bundle)
    {
        $this->authorize('update', $bundle);

        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => new BundleResource($bundle->load('skus')),
            'skus' => SkuSelectResource::collection(Sku::getAvailableForBundles()),
            'branches' => Branch::getMinimalList()
        ]);
    }

    public function update(BundleRequest $request, Bundle $bundle, UpsertBundleAction $action)
    {
        $this->authorize('update', $bundle);

        $action->execute(BundleDTO::fromRequest($request), $bundle);

        return redirect()->route('admin.bundles.index')->with('success', 'Pack actualizado.');
    }

    public function destroy(Bundle $bundle, DeleteBundleAction $action)
    {
        $this->authorize('delete', $bundle);

        $action->execute($bundle);

        return redirect()->route('admin.bundles.index')->with('success', 'Pack eliminado.');
    }
}