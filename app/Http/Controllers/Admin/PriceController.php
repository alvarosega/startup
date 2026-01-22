<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLot;
use App\Models\Branch;
use App\Models\Price;
use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener combinaciones únicas de Stock (Sucursal + SKU)
        // Solo nos interesa donde hay existencias físicas.
        $query = InventoryLot::query()
            ->select('branch_id', 'sku_id')
            ->where('quantity', '>', 0)
            ->distinct();

        // Filtro opcional por sucursal
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filtro por búsqueda (Requiere Join porque InventoryLot no tiene nombre)
        if ($request->search) {
            $query->whereHas('sku', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"));
            });
        }

        // Cargar relaciones necesarias para la vista
        $stockItems = $query->with([
                'branch', 
                'sku.product.category',
                'sku.prices' // Traemos todos para filtrar en memoria o subquery
            ])
            ->get()
            // 2. AGRUPAR POR SUCURSAL
            // Esto crea la estructura: { "Sucursal Norte": [Items...], "Sucursal Sur": [Items...] }
            ->groupBy(function($item) {
                return $item->branch->name;
            });

        return Inertia::render('Admin/Prices/Index', [
            'stockByBranch' => $stockItems, // Estructura agrupada
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'filters' => $request->only(['search', 'branch_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:skus,id',
            'branch_id' => 'required|exists:branches,id',
            'final_price' => 'required|numeric|min:0',
        ]);

        $sku = Sku::findOrFail($request->sku_id);
        
        // Usamos el helper del modelo para mantener la consistencia (Borrar anterior -> Crear nuevo)
        $sku->updatePrice($request->final_price, $request->branch_id);

        return back()->with('success', 'Precio de sucursal actualizado.');
    }
}