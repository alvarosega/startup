<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Cargar SKUs activos con su Producto Padre
        $query = Sku::with(['product.brand'])
            ->where('is_active', true)
            ->whereHas('product', function($q) {
                $q->where('is_active', true);
            });

        // 2. Si el usuario está LOGUEADO, cargamos el precio
        if ($request->user()) {
            // Aquí podríamos filtrar precios por Sucursal ($request->user()->branch_id)
            // Por ahora cargamos el precio base general
            $query->with('currentPrice');
        }

        $skus = $query->paginate(12);

        // 3. Transformación de datos (Para limpiar el JSON que va al frontend)
        $mappedSkus = $skus->through(function ($sku) use ($request) {
            return [
                'id' => $sku->id,
                'name' => $sku->product->name . ' - ' . $sku->name,
                'description' => $sku->product->description,
                'image' => $sku->image, // Usa el Accessor que creamos
                'brand' => $sku->product->brand->name,
                'code' => $sku->code,
                // Lógica de Precio: Solo si existe y el usuario está logueado
                'price' => $request->user() && $sku->currentPrice 
                    ? (float) $sku->currentPrice->final_price 
                    : null,
                'is_guest' => !$request->user(),
            ];
        });

        return Inertia::render('Shop/Index', [
            'skus' => $mappedSkus,
        ]);
    }
    
    public function show($id)
    {
        // Pendiente para la vista de detalle
    }
}