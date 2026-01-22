<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Price; 
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SkuController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('create', Sku::class);
        // Validación estricta
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:skus,code',
            'conversion_factor' => 'required|numeric|min:1',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0', // Precio base
        ]);

        // 1. Crear el SKU
        $sku = Sku::create([
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'code' => $data['code'],
            'conversion_factor' => $data['conversion_factor'],
            'weight' => $data['weight'] ?? 0,
            'is_active' => true
        ]);

        // 2. Crear el Precio Inicial
        Price::create([
            'sku_id' => $sku->id,
            'list_price' => $data['price'],
            'final_price' => $data['price'],
            'min_quantity' => 1,
            'valid_from' => now()
        ]);

        return back()->with('message', 'SKU añadido correctamente.');
    }

    public function destroy($id)
    {
        $sku = Sku::findOrFail($id);
        $this->authorize('delete', $sku);
        // Aquí podrías agregar validación de stock antes de borrar
        $sku->delete();
        return back()->with('message', 'SKU archivado.');
    }

    // No implementamos create/store aquí. 
    // Razón: Un SKU requiere contexto de un Producto Padre.
    
    public function edit($id)
    {
        $sku = Sku::findOrFail($id);
        // Aunque redirija, validamos permiso
        $this->authorize('update', $sku->product); // Usamos policy de producto padre
        
        return redirect()->route('admin.products.edit', $sku->product_id);
    }
}