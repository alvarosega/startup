<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLot;
use App\Models\Branch;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura
use App\DTOs\Price\PriceData;
use App\Http\Requests\Price\UpdatePriceRequest;
use App\Actions\Price\UpdateBranchPrice;

class PriceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // $this->authorize('viewAny', Price::class); // Si tienes Policy

        // 1. Consulta optimizada de Stock disponible
        $query = InventoryLot::query()
            ->select('branch_id', 'sku_id')
            ->where('quantity', '>', 0)
            ->distinct();

        // Filtros
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->search) {
            $query->whereHas('sku', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"));
            });
        }

        // Ejecución y Carga de Relaciones
        $stockItems = $query->with([
                'branch:id,name', 
                'sku:id,product_id,name,code',
                'sku.product:id,name,category_id', // Solo campos necesarios
                'sku.product.category:id,name',
                'sku.prices' // Traemos historial para filtrar en frontend/backend
            ])
            ->get()
            ->groupBy(fn($item) => $item->branch->name); // Agrupación

        return Inertia::render('Admin/Prices/Index', [
            'stockByBranch' => $stockItems,
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'filters' => $request->only(['search', 'branch_id']),
        ]);
    }

    public function store(UpdatePriceRequest $request, UpdateBranchPrice $action)
    {
        // $this->authorize('create', Price::class);

        $data = PriceData::fromRequest($request);
        $action->execute($data);

        return back()->with('success', 'Precio actualizado correctamente.');
    }
}