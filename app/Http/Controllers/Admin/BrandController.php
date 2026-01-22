<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\Category;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        // Eager loading para evitar N+1
        $brands = Brand::with('provider')->orderBy('name')->get();

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/SuperAdmin/Brands/Create', [
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        // Sincronizar tabla pivot
        if (!empty($data['categories'])) {
            $brand->categories()->sync($data['categories']);
        }

        return redirect()->route('admin.brands.index')->with('message', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand)
    {
        // Cargamos la relación pivot para que el formulario sepa cuáles marcar
        $brand->load('categories');

        return Inertia::render('Admin/SuperAdmin/Brands/Edit', [
            'brand' => $brand,
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            // Enviamos array simple de IDs [1, 5, 8] al frontend
            'current_categories' => $brand->categories->pluck('id')
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        // Sincronización Pivot (incluso si es array vacío para borrar todas)
        $brand->categories()->sync($data['categories'] ?? []);

        return redirect()->route('admin.brands.index')->with('message', 'Marca actualizada.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('message', 'Marca eliminada.');
    }
}