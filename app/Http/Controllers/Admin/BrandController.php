<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\Category;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Clean Architecture
use App\DTOs\Brand\BrandData;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Actions\Brand\CreateBrand;
use App\Actions\Brand\UpdateBrand;
use App\Http\Resources\BrandResource;

class BrandController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Brand::class);

        $brands = Brand::with(['provider', 'categories'])->orderBy('name')->paginate(15);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => BrandResource::collection($brands)->resolve(), // .resolve() IMPORTANTE
            'can_manage' => auth()->user()->can('create', Brand::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Brand::class);

        return Inertia::render('Admin/Brands/Create', [
            // Listas ligeras para selectores
            'providers' => Provider::where('is_active', true)
                ->orderBy('commercial_name')
                ->get(['id', 'commercial_name', 'company_name'])
                ->map(fn($p) => ['id' => $p->id, 'name' => $p->commercial_name ?? $p->company_name]),
                
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreBrandRequest $request, CreateBrand $action)
    {
        $this->authorize('create', Brand::class);
        
        $data = BrandData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.brands.index')->with('message', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);
        $brand->load(['categories', 'provider']);

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => (new BrandResource($brand))->resolve(), // .resolve() IMPORTANTE
            
            // Reutilizamos la lógica de carga de listas
            'providers' => Provider::where('is_active', true)
                ->orderBy('commercial_name')
                ->get(['id', 'commercial_name', 'company_name'])
                ->map(fn($p) => ['id' => $p->id, 'name' => $p->commercial_name ?? $p->company_name]),
                
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpdateBrand $action)
    {
        $this->authorize('update', $brand);

        $data = BrandData::fromRequest($request);
        $action->execute($brand, $data);

        return redirect()->route('admin.brands.index')->with('message', 'Marca actualizada.');
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('delete', $brand);
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('message', 'Marca eliminada.');
    }
}