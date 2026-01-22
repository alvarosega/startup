<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Sku;
use app\Models\User;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Purchase::class);
        $user = auth()->user();

        // CAMBIO IMPORTANTE: Agregamos 'user.profile' al with()
        $query = Purchase::with([
                'provider', 
                'branch', 
                'user.profile', // <--- ESTO ES CRUCIAL PARA OBTENER EL NOMBRE
                'inventoryLots.sku.product'
            ]) 
            ->withCount('inventoryLots')
            ->orderBy('purchase_date', 'desc');

        if ($user->hasRole('branch_admin')) {
            $query->where('branch_id', $user->branch_id);
        }

        return Inertia::render('Admin/Inventory/Purchases/Index', [
            'purchases' => $query->paginate(20),
            'is_branch_admin' => $user->hasRole('branch_admin') 
        ]);
    }
    public function create()
    {
        $this->authorize('create', Purchase::class);
        $user = auth()->user();

        // LOGICA DE SUCURSALES (Frontend)
        if ($user->hasRole('branch_admin')) {
            // Solo envío SU sucursal
            $branches = Branch::where('id', $user->branch_id)->get(['id', 'name']);
        } else {
            // Envío todas
            $branches = Branch::where('is_active', true)->get(['id', 'name']);
        }

        return Inertia::render('Admin/Inventory/Purchases/Create', [
            'branches' => $branches,
            'providers' => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name']),
            // SKUs ligeros para el buscador
            'skus' => Sku::with('product')
                ->where('is_active', true)
                ->get()
                ->map(function($s) {
                    return [
                        'id' => $s->id,
                        'product_id' => $s->product_id, 
                        'full_name' => ($s->product->name ?? 'Unknown') . ' - ' . $s->name,
                        'factor' => (float)$s->conversion_factor,
                        'code' => $s->code
                    ];
                })
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $this->authorize('create', Purchase::class);
        
        $data = $request->validated();
        $user = $request->user();

        // --- BRANCH LOCKING (SEGURIDAD CRÍTICA) ---
        // Ignoramos lo que envíe el formulario si es Branch Admin. Usamos su ID real.
        if ($user->hasRole('branch_admin')) {
            $targetBranchId = $user->branch_id;
        } else {
            // Para Super Admin es obligatorio elegir
            if (empty($data['branch_id'])) {
                return back()->withErrors(['branch_id' => 'La sucursal es requerida.']);
            }
            $targetBranchId = $data['branch_id'];
        }

        try {
            DB::transaction(function () use ($data, $user, $targetBranchId) {
                
                $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);

                // 1. Crear Compra con Datos Financieros
                $purchase = Purchase::create([
                    'branch_id' => $targetBranchId,
                    'provider_id' => $data['provider_id'],
                    'user_id' => $user->id,
                    'document_number' => $data['document_number'],
                    'purchase_date' => $data['purchase_date'],
                    'payment_type' => $data['payment_type'],
                    'payment_due_date' => $data['payment_type'] === 'CREDIT' ? $data['payment_due_date'] : null,
                    'total_amount' => $total,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'COMPLETED'
                ]);

                // 2. Procesar Lotes y Movimientos
                foreach ($data['items'] as $item) {
                    
                    // a) Crear Lote (Stock Físico)
                    $lot = InventoryLot::create([
                        'purchase_id' => $purchase->id,
                        'branch_id' => $targetBranchId,
                        'sku_id' => $item['sku_id'],
                        'quantity' => $item['quantity'],
                        'initial_quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'expiration_date' => $item['expiration_date'] ?? null
                    ]);

                    // b) Registrar en Kardex
                    InventoryMovement::create([
                        'branch_id' => $targetBranchId,
                        'sku_id' => $item['sku_id'],
                        'inventory_lot_id' => $lot->id,
                        'user_id' => $user->id,
                        'type' => 'purchase',
                        'quantity' => $item['quantity'], // Positivo = Entrada
                        'unit_cost' => $item['unit_cost'],
                        'reference' => 'Compra #' . $purchase->document_number
                    ]);
                }
            });

            return redirect()->route('admin.purchases.index')->with('message', 'Compra registrada correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error crítico: ' . $e->getMessage()])->withInput();
        }
    }
}