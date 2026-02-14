<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLot;
use App\Models\Branch;
use app\Models\User;
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
        $user = auth()->user();
        
        $query = InventoryLot::with(['sku.product.brand', 'branch'])
            ->select(
                'branch_id',
                'sku_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                DB::raw('SUM(quantity * unit_cost) / NULLIF(SUM(quantity), 0) as avg_cost')
            )
            ->groupBy('branch_id', 'sku_id')
            ->havingRaw('SUM(quantity) > 0');

        // --- LÓGICA DE SEGURIDAD Y FILTROS ---
        
        // 1. Si es Branch Admin, FORZAMOS el filtro a su sucursal
        if ($user->hasRole('branch_admin')) {
            $query->where('branch_id', $user->branch_id);
            // Solo le enviamos SU sucursal para los filtros del frontend
            $branches = Branch::where('id', $user->branch_id)->select('id', 'name')->get();
        } else {
            // 2. Si es Super Admin, permitimos filtrar por lo que venga en el request
            if ($request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }
            $branches = Branch::where('is_active', true)->select('id', 'name')->get();
        }

        // Búsqueda General
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
            'branches' => $branches, // Si es Branch Admin, esto es un array de 1 elemento
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
/**
     * API: Obtener lista de productos con stock positivo en una sucursal específica.
     * Usado para llenar el select de Transferencias.
     */
    public function getStockByBranch($branchId)
    {
        // Validar seguridad: Si es Branch Admin, solo puede consultar SU sucursal
        $user = auth()->user();
        if ($user->hasRole('branch_admin') && $user->branch_id != $branchId) {
            abort(403, 'No autorizado para ver stock de otra sucursal.');
        }

        $products = \App\Models\InventoryLot::where('branch_id', $branchId)
            ->where('quantity', '>', 0) // Solo lo que tiene stock físico
            ->join('skus', 'inventory_lots.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->select(
                'skus.id',
                'skus.code',
                'skus.name as sku_name',
                'products.name as product_name',
                // Sumamos todo el stock disponible (excluyendo lo reservado si usas esa lógica)
                \Illuminate\Support\Facades\DB::raw('SUM(inventory_lots.quantity - inventory_lots.reserved_quantity) as available_stock')
            )
            ->groupBy('skus.id', 'skus.code', 'skus.name', 'products.name')
            ->having('available_stock', '>', 0) // Doble chequeo
            ->orderBy('products.name')
            ->get();

        return response()->json($products);
    }
}