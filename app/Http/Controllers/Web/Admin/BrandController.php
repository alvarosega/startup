<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\Category;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\DTOs\Admin\Brand\BrandData;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Actions\Admin\Brand\CreateBrand;
use App\Actions\Admin\Brand\ListBrands; // Necesitas crear esta Action
use App\Http\Resources\Admin\Brand\BrandResource;
use Inertia\Response;
use Illuminate\Http\Request; 


class BrandController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, ListBrands $action): Response
    {
        $this->authorize('viewAny', Brand::class);

        $brands = $action->execute($request->search);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => BrandResource::collection($brands),
            'filters' => $request->only(['search']),
            'can_manage' => $request->user()->can('create', Brand::class)
        ]);
    }
    public function create()
    {
        $this->authorize('create', Brand::class);
    
        return Inertia::render('Admin/Brands/Create', [
            // Uso de scopes y métodos estáticos definidos en modelos
            'providers' => Provider::active()->orderBy('company_name')->get(['id', 'company_name']),
            'categories' => Category::active()->roots()->orderBy('name')->get(['id', 'name'])
        ]);
    }
    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);
    
        return Inertia::render('Admin/Brands/Edit', [
            'brand' => new BrandResource($brand),
            'providers' => Provider::active()->orderBy('company_name')->get(['id', 'company_name']),
            'categories' => Category::active()->roots()->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreBrandRequest $request, CreateBrand $action)
    {
        $this->authorize('create', Brand::class);
        
        $action->execute(BrandData::fromRequest($request));

        return redirect()->route('admin.brands.index')->with('message', 'Protocolo de creación de marca completado.');
    }
}