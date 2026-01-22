<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLot;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Muestra la "Sábana de Inventario" (Stock Consolidado)
     */
    public function index(Request $request)
    {
        // Agrupamos por SKU y Sucursal para mostrar totales
        $query = InventoryLot::with(['sku.product.brand', 'branch'])
            ->select(
                'branch_id',
                'sku_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                // Costo Promedio Ponderado simple (Cuidado: esto es aproximado)
                DB::raw('SUM(quantity * unit_cost) / NULLIF(SUM(quantity), 0) as avg_cost')
            )
            ->groupBy('branch_id', 'sku_id')
            ->havingRaw('SUM(quantity) > 0'); // Solo stock positivo

        // Filtro por Sucursal
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        // Búsqueda (Producto, Código, Marca)
        if ($request->search) {
            $query->whereHas('sku', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhereHas('product', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%")
                         ->orWhereHas('brand', function($q3) use ($request) {
                             $q3->where('name', 'like', "%{$request->search}%");
                         });
                  });
            });
        }

        $inventory = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Inventory/Index', [
            'inventory' => $inventory,
            'branches' => Branch::where('is_active', true)->select('id', 'name')->get(),
            'filters' => $request->only(['branch_id', 'search'])
        ]);
    }

    /**
     * API: Buscar lotes disponibles para selectores (Transferencias/Bajas)
     */
    public function search(Request $request)
    {
        $term = $request->input('term');
        $branchId = $request->input('branch_id');

        $lots = InventoryLot::with(['sku.product'])
            ->where('quantity', '>', 0) // Solo lotes con stock
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereHas('sku', function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhereHas('product', function($q2) use ($term) {
                      $q2->where('name', 'like', "%{$term}%");
                  });
            })
            ->take(20)
            ->get()
            ->map(function($lot) {
                return [
                    'id' => $lot->id,
                    'text' => "{$lot->sku->product->name} - {$lot->sku->name} (Lote: {$lot->lot_code})",
                    'stock' => $lot->quantity,
                    'sku_id' => $lot->sku_id
                ];
            });

        return response()->json($lots);
    }
    
    // Eliminamos create, store, edit, update de aquí.
    // Esa responsabilidad ahora es exclusiva de PurchaseController.
}