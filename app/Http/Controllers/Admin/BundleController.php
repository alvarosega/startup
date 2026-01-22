<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BundleController extends Controller
{
    /**
     * Muestra la lista de Packs
     */
    public function index()
    {
        $bundles = Bundle::withCount(['skus', 'reviews']) // Contamos productos y reseñas
            ->withAvg('reviews', 'rating') // Promedio de estrellas
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => $bundles
        ]);
    }

    /**
     * Muestra el formulario de creación
     */
    public function create()
    {
        // Necesitamos enviar todos los SKUs para que el admin elija qué poner en el pack
        // Cargamos también el producto padre para mostrar el nombre completo
        $skus = Sku::with('product')
            ->where('is_active', true)
            ->get()
            ->map(function ($sku) {
                return [
                    'id' => $sku->id,
                    'code' => $sku->code,
                    'name' => $sku->product->name . ' ' . $sku->name // Ej: "Coca Cola 2L"
                ];
            });

        return Inertia::render('Admin/Bundles/Create', [
            'skus' => $skus
        ]);
    }

    /**
     * Guarda el nuevo Pack en la BD
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fixed_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1', // Debe tener al menos 1 producto
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Crear el Bundle
                $bundle = Bundle::create([
                    'name' => $request->name,
                    'slug' => \Illuminate\Support\Str::slug($request->name) . '-' . uniqid(),
                    'description' => $request->description,
                    'fixed_price' => $request->fixed_price, // Puede ser null
                    'is_active' => $request->is_active,
                    // 'image_path' => ... (Lógica de imagen pendiente si la deseas agregar luego)
                ]);

                // 2. Asociar los Items (SKUs) en la tabla pivote
                // Preparamos el array para sync: [sku_id => ['quantity' => 2], ...]
                $itemsSync = [];
                foreach ($request->items as $item) {
                    // Si seleccionan el mismo producto 2 veces, sumamos cantidades (lógica defensiva)
                    if (isset($itemsSync[$item['sku_id']])) {
                        $itemsSync[$item['sku_id']]['quantity'] += $item['quantity'];
                    } else {
                        $itemsSync[$item['sku_id']] = ['quantity' => $item['quantity']];
                    }
                }

                $bundle->skus()->sync($itemsSync);
            });

            return redirect()->route('admin.bundles.index')
                ->with('success', 'Pack creado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al guardar el pack: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar un pack (Soft Delete)
     */
    public function destroy(string $id)
    {
        $bundle = Bundle::findOrFail($id);
        $bundle->delete();

        return redirect()->route('admin.bundles.index')
            ->with('success', 'Pack eliminado.');
    }
/**
     * Muestra el formulario de edición
     */
    public function edit(string $id)
    {
        $bundle = Bundle::with('skus')->findOrFail($id);
        
        // Transformamos los items actuales para que Vue los entienda fácil
        $currentItems = $bundle->skus->map(function($sku) {
            return [
                'sku_id' => $sku->id,
                'quantity' => $sku->pivot->quantity
            ];
        });

        // Lista de todos los productos para el select
        $skus = Sku::with('product')
            ->where('is_active', true)
            ->get()
            ->map(function ($sku) {
                return [
                    'id' => $sku->id,
                    'code' => $sku->code,
                    'name' => $sku->product->name . ' ' . $sku->name
                ];
            });

        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => $bundle,
            'currentItems' => $currentItems,
            'skus' => $skus
        ]);
    }

    /**
     * Actualiza el Pack
     */
    public function update(Request $request, string $id)
    {
        $bundle = Bundle::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fixed_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request, $bundle) {
                // 1. Actualizar datos básicos
                $bundle->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'fixed_price' => $request->fixed_price,
                    'is_active' => $request->is_active,
                ]);

                // 2. Sincronizar Items (Borra los viejos y pone los nuevos)
                $itemsSync = [];
                foreach ($request->items as $item) {
                    if (isset($itemsSync[$item['sku_id']])) {
                        $itemsSync[$item['sku_id']]['quantity'] += $item['quantity'];
                    } else {
                        $itemsSync[$item['sku_id']] = ['quantity' => $item['quantity']];
                    }
                }
                $bundle->skus()->sync($itemsSync);
            });

            return redirect()->route('admin.bundles.index')
                ->with('success', 'Pack actualizado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
}