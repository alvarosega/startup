<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Sku;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura
use App\DTOs\Purchase\PurchaseData;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Actions\Purchase\CreatePurchase;
use App\Http\Resources\PurchaseResource;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Purchase::class);
        $user = auth()->user();

        $query = Purchase::with([
                'provider', 
                'branch', 
                'user', // Simplificado, el Resource maneja el display name
                'inventoryLots.sku.product'
            ])
            ->withCount('inventoryLots')
            ->orderBy('purchase_date', 'desc');

        if ($user->hasRole('branch_admin')) {
            $query->where('branch_id', $user->branch_id);
        }

        $purchases = $query->paginate(20);

        return Inertia::render('Admin/Inventory/Purchases/Index', [
            // Usamos resolve() pero reconstruimos la paginación para Vue
            'purchases' => [
                'data' => PurchaseResource::collection($purchases)->resolve(),
                'links' => $purchases->linkCollection()->toArray(),
                'total' => $purchases->total(),
                'current_page' => $purchases->currentPage(),
                // ...otros metadatos si los necesitas
            ],
            'is_branch_admin' => $user->hasRole('branch_admin') 
        ]);
    }

    public function create()
    {
        $this->authorize('create', Purchase::class);
        $user = auth()->user();

        // Lógica de Sucursales para el Select
        $branches = ($user->hasRole('branch_admin'))
            ? Branch::where('id', $user->branch_id)->get(['id', 'name'])
            : Branch::where('is_active', true)->get(['id', 'name']);

        // Data ligera para selectores
        return Inertia::render('Admin/Inventory/Purchases/Create', [
            'branches' => $branches,
            'providers' => Provider::where('is_active', true)
                ->orderBy('commercial_name')
                ->get(['id', 'commercial_name']),
                
            'skus' => Sku::with('product:id,name')
                ->where('is_active', true)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'product_id' => $s->product_id,
                    'full_name' => ($s->product->name ?? 'Unknown') . ' - ' . $s->name,
                    'factor' => (float) $s->conversion_factor,
                    'code' => $s->code
                ])
        ]);
    }

    public function store(StorePurchaseRequest $request, CreatePurchase $action)
    {
        $this->authorize('create', Purchase::class);
        
        $data = PurchaseData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.purchases.index')->with('success', 'Compra registrada correctamente.');
    }
}