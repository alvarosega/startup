<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
// 1. IMPORTANTE
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandController extends Controller
{
    // 2. IMPORTANTE
    use AuthorizesRequests;

    public function index()
    {
        // 3. SEGURIDAD
        $this->authorize('viewAny', Brand::class);

        // Eager loading para evitar N+1
        $brands = Brand::with('provider')->orderBy('name')->get();

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
            // 4. PERMISO AL FRONTEND
            'can_manage' => auth()->user()->can('create', Brand::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Brand::class);

        return Inertia::render('Admin/Brands/Create', [
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Brand::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'manufacturer' => 'nullable|string|max:255',
            'origin_country_code' => 'nullable|string|size:2',
            'tier' => 'required|in:Economy,Standard,Premium,Luxury',
            'website' => 'nullable|url',
            'is_active' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }
        
        // Slug auto
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $brand = Brand::create($validated);

        if (!empty($validated['categories'])) {
            $brand->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.brands.index')->with('message', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);

        $brand->load('categories');

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand,
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'current_categories' => $brand->categories->pluck('id')
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $this->authorize('update', $brand);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands')->ignore($brand->id)],
            'slug' => 'nullable|string|max:255',
            'provider_id' => 'nullable|exists:providers,id',
            'manufacturer' => 'nullable|string|max:255',
            'origin_country_code' => 'nullable|string|size:2',
            'tier' => 'required|in:Economy,Standard,Premium,Luxury',
            'website' => 'nullable|url',
            'is_active' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('brands', 'public');
        }

        if (empty($validated['slug']) && $brand->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $brand->update($validated);

        $brand->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('admin.brands.index')->with('message', 'Marca actualizada.');
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('delete', $brand);
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('message', 'Marca eliminada.');
    }
}