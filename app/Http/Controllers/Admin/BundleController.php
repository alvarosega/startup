<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Sku;
use App\Http\Requests\Bundle\BundleRequest;
use App\DTOs\Bundle\BundleDTO;
use App\Actions\Bundle\UpsertBundleAction;
use App\Http\Resources\BundleResource;
use App\Http\Resources\SkuSelectResource;
use Inertia\Inertia;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::withCount(['skus'])->latest()->get();
        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => BundleResource::collection($bundles)
        ]);
    }

    public function create()
    {
        $skus = Sku::with('product')->where('is_active', true)->get();
        return Inertia::render('Admin/Bundles/Create', [
            'skus' => SkuSelectResource::collection($skus)
        ]);
    }

    public function store(BundleRequest $request, UpsertBundleAction $action)
    {
        $action->execute(BundleDTO::fromRequest($request));
        return redirect()->route('admin.bundles.index')->with('success', 'Pack creado.');
    }

    public function edit(Bundle $bundle)
    {
        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => new BundleResource($bundle->load('skus')),
            'skus' => SkuSelectResource::collection(Sku::with('product')->where('is_active', true)->get())
        ]);
    }

    public function update(BundleRequest $request, Bundle $bundle, UpsertBundleAction $action)
    {
        // El DTO ahora tomará los datos validados
        $action->execute(BundleDTO::fromRequest($request), $bundle);
        return redirect()->route('admin.bundles.index')->with('success', 'Pack actualizado.');
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Pack eliminado.');
    }
}