<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SkuController extends Controller
{
    public function index(Request $request)
    {
        // Eager loading optimizado: Producto, Marca y Categoría
        $query = Sku::with(['product.brand', 'product.category']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                // Búsqueda en columnas en INGLÉS
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhereHas('product', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $skus = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($sku) {
                return [
                    'id' => $sku->id,
                    'product_id' => $sku->product_id,
                    'code' => $sku->code,
                    'name' => $sku->name,
                    'factor' => $sku->conversion_factor,
                    // Manejo de nulos por si se borró el padre (SoftDelete)
                    'product_name' => $sku->product ? $sku->product->name : 'Producto Eliminado',
                    'brand_name' => $sku->product?->brand ? $sku->product->brand->name : '-',
                    'category_name' => $sku->product?->category ? $sku->product->category->name : '-',
                ];
            });

        return Inertia::render('Admin/Skus/Index', [
            'skus' => $skus,
            'filters' => $request->only(['search'])
        ]);
    }

    // No implementamos create/store aquí. 
    // Razón: Un SKU requiere contexto de un Producto Padre.
    
    public function edit($id)
    {
        $sku = Sku::findOrFail($id);
        // Estrategia: Redirigir al editor del Padre para mantener integridad
        return redirect()->route('admin.products.edit', $sku->product_id);
    }
    
    public function destroy($id)
    {
        $sku = Sku::findOrFail($id);
        
        // Validación de Stock (Opcional pero recomendada)
        // if ($sku->inventoryLots()->where('quantity', '>', 0)->exists()) {
        //    return back()->withErrors(['error' => 'No se puede eliminar: Tiene stock activo.']);
        // }

        $sku->delete();
        return back()->with('message', 'SKU archivado correctamente.');
    }
}