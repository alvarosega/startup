<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Sku;
use App\Models\Branch;
use App\Http\Requests\Bundle\BundleRequest;
use App\DTOs\Bundle\BundleDTO;
use App\Actions\Bundle\UpsertBundleAction;
use App\Http\Resources\BundleResource;
use App\Http\Resources\SkuSelectResource;
use Inertia\Inertia;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Cargar 'branch' (para el nombre) y 'skus' (para ver los items en la tabla)
        $query = Bundle::with(['branch', 'skus']) 
            ->withCount(['skus'])
            ->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => BundleResource::collection($query->paginate(10)->withQueryString()),
            'branches' => Branch::select('id', 'name')->get() 
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bundles/Create', [
            'skus' => SkuSelectResource::collection(Sku::with('product')->where('is_active', true)->get()),
            'branches' => Branch::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function store(BundleRequest $request, UpsertBundleAction $action)
    {
        $action->execute(BundleDTO::fromRequest($request));
        return redirect()->route('admin.bundles.index')->with('success', 'Pack creado correctamente.');
    }

    public function edit(Bundle $bundle)
    {
        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => new BundleResource($bundle->load('skus')),
            'skus' => SkuSelectResource::collection(Sku::with('product')->where('is_active', true)->get()),
            'branches' => Branch::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function update(BundleRequest $request, Bundle $bundle, UpsertBundleAction $action)
    {
        $action->execute(BundleDTO::fromRequest($request), $bundle);
        return redirect()->route('admin.bundles.index')->with('success', 'Pack actualizado.');
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Pack eliminado.');
    }
}