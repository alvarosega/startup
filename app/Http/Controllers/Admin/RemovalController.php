<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemovalRequest;
use App\Models\RemovalItem;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RemovalController extends Controller
{
    public function index()
    {
        $removals = RemovalRequest::with(['branch', 'requester', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Inventory/Removals/Index', ['removals' => $removals]);
    }

    public function create()
    {
        // Reutilizamos la lógica de stock real (Igual que en Transferencias)
        $inventory = InventoryLot::join('skus', 'inventory_lots.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->where('inventory_lots.quantity', '>', 0)
            ->select(
                'inventory_lots.branch_id',
                'inventory_lots.sku_id as id',
                'products.name as product_name',
                'skus.name as sku_name',
                DB::raw('SUM(inventory_lots.quantity - inventory_lots.reserved_quantity) as stock_real')
            )
            ->groupBy('inventory_lots.branch_id', 'inventory_lots.sku_id', 'products.name', 'skus.name')
            ->having('stock_real', '>', 0)
            ->get();

        return Inertia::render('Admin/Inventory/Removals/Create', [
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'inventory' => $inventory
        ]);
    }

    // --- SOLICITUD (MANAGER) ---
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'reason' => 'required|in:expiration,damage,theft,internal_use,admin_error',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($data, $request) {
                
                // 1. Crear Cabecera Pending
                $removal = RemovalRequest::create([
                    'branch_id' => $data['branch_id'],
                    'user_id' => $request->user()->id,
                    'status' => 'pending',
                    'reason' => $data['reason'],
                    'notes' => $data['notes']
                ]);

                // 2. Reservar Stock FIFO
                foreach ($data['items'] as $item) {
                    $qtyNeeded = $item['quantity'];

                    // Buscar lotes libres
                    $lots = InventoryLot::where('branch_id', $data['branch_id'])
                        ->where('sku_id', $item['sku_id'])
                        ->whereRaw('(quantity - reserved_quantity) > 0')
                        ->orderBy('expiration_date', 'asc')
                        ->get();

                    if ($lots->sum('quantity') - $lots->sum('reserved_quantity') < $qtyNeeded) {
                        throw new \Exception("Stock insuficiente para el SKU ID {$item['sku_id']}");
                    }

                    foreach ($lots as $lot) {
                        if ($qtyNeeded <= 0) break;
                        
                        $available = $lot->quantity - $lot->reserved_quantity;
                        $take = min($available, $qtyNeeded);

                        // AQUÍ LA CLAVE: No restamos 'quantity' todavía. Solo aumentamos 'reserved'.
                        // El stock físico sigue ahí, pero "bloqueado".
                        $lot->increment('reserved_quantity', $take);
                        
                        // Guardamos qué lote específico se reservó
                        RemovalItem::create([
                            'removal_request_id' => $removal->id,
                            'inventory_lot_id' => $lot->id,
                            'quantity' => $take,
                            'unit_cost' => $lot->unit_cost
                        ]);

                        $qtyNeeded -= $take;
                    }
                }
            });

            return redirect()->route('admin.removals.index')->with('message', 'Solicitud creada. Stock bloqueado esperando aprobación.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // --- APROBACIÓN (SUPER ADMIN) ---
    public function approve(Request $request, $id)
    {
        // Verificar Rol (Esto también se hace en middleware, pero validamos doble)
        if (!$request->user()->hasRole('super_admin')) {
            abort(403, 'Solo el Super Admin puede aprobar bajas.');
        }

        $removal = RemovalRequest::with('items.lot')->findOrFail($id);

        if ($removal->status !== 'pending') return back();

        try {
            DB::transaction(function () use ($removal, $request) {
                
                foreach ($removal->items as $item) {
                    $lot = $item->lot;
                    
                    // 1. Consumir la reserva y el físico
                    // Restamos de reserved (porque ya no está reservado, se va)
                    // Restamos de quantity (porque sale del almacén)
                    $lot->decrement('reserved_quantity', $item->quantity);
                    $lot->decrement('quantity', $item->quantity);

                    // 2. Registrar Kardex (Salida Definitiva)
                    InventoryMovement::create([
                        'branch_id' => $removal->branch_id,
                        'sku_id' => $lot->sku_id, // Acceso directo al SKU del lote
                        'inventory_lot_id' => $lot->id,
                        'user_id' => $request->user()->id, // El que aprueba firma la salida
                        'type' => 'removal_out',
                        'quantity' => -$item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'reference' => 'Baja Aprobada ' . $removal->code . ' (' . $removal->reason . ')'
                    ]);
                }

                $removal->update([
                    'status' => 'approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now()
                ]);
            });

            return back()->with('message', 'Baja aprobada y stock descontado.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // --- RECHAZO (SUPER ADMIN) ---
    public function reject(Request $request, $id)
    {
        if (!$request->user()->hasRole('super_admin')) abort(403);

        $removal = RemovalRequest::with('items.lot')->findOrFail($id);
        if ($removal->status !== 'pending') return back();

        try {
            DB::transaction(function () use ($removal, $request) {
                
                foreach ($removal->items as $item) {
                    $lot = $item->lot;
                    
                    // LIBERAR STOCK: Restamos de reserved, pero NO tocamos quantity.
                    // El stock vuelve a estar "Libre" para venderse.
                    $lot->decrement('reserved_quantity', $item->quantity);
                }

                $removal->update([
                    'status' => 'rejected',
                    'approved_by' => $request->user()->id, // Quien rechazó
                    'approved_at' => now()
                ]);
            });

            return back()->with('message', 'Solicitud rechazada. Stock liberado.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}