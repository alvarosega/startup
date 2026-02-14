<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\Branch;
use App\Models\Sku;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Http\Requests\Transfer\ReceiveTransferRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransferController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Transfer::class);
        $user = auth()->user();

        $query = Transfer::with(['origin', 'destination', 'sender'])
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        if (!$user->hasRole('super_admin')) {
            $query->where(function($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('destination_branch_id', $user->branch_id);
            });
        }

        return Inertia::render('Admin/Inventory/Transfers/Index', [
            'transfers' => $query->paginate(20),
            'user_branch_id' => $user->branch_id
        ]);
    }

    public function create()
    {
        $this->authorize('create', Transfer::class);
        $user = auth()->user();

        // 1. ORIGENES PERMITIDOS (Aquí está la lógica del bloqueo)
        if ($user->hasRole('super_admin')) {
            // Super Admin puede sacar de cualquier lado
            $origins = Branch::where('is_active', true)->get(['id', 'name']);
        } else {
            // Branch Admin SOLO puede sacar de SU sucursal
            $origins = Branch::where('id', $user->branch_id)->get(['id', 'name']);
        }

        // 2. DESTINOS POSIBLES (Todas las sucursales activas)
        $destinations = Branch::where('is_active', true)->get(['id', 'name']);

        // 3. PRODUCTOS (SKUs) para el buscador
        $skus = Sku::with('product')
            ->where('is_active', true)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'full_name' => ($s->product->name ?? 'Sin Nombre') . ' - ' . $s->name,
                    'code' => $s->code
                ];
            });

        // ENVIAMOS LAS VARIABLES EXACTAS QUE PIDE VUE
        return Inertia::render('Admin/Inventory/Transfers/Create', [
            'origins' => $origins,
            'destinations' => $destinations,
            'skus' => $skus
        ]);
    }

    // ... (Mantén tus métodos store, show y receive como ya los tenías aprobados) ...
    // Asegúrate de incluir el método store y receive completos que te di en la respuesta anterior
    
    public function show(Transfer $transfer)
    {
        // Cargamos relaciones necesarias para la vista Show
        $transfer->load(['origin', 'destination', 'sender', 'items.sku.product']);
        return Inertia::render('Admin/Inventory/Transfers/Show', ['transfer' => $transfer]);
    }

    public function store(StoreTransferRequest $request)
    {
        $this->authorize('create', Transfer::class);
        $data = $request->validated();
        $user = $request->user();

        // SEGURIDAD: FORZAR ORIGEN
        if ($user->hasRole('super_admin')) {
            $originId = $data['origin_branch_id'];
        } else {
            $originId = $user->branch_id;
        }

        try {
            DB::transaction(function () use ($data, $user, $originId) {
                // Crear Transferencia
                $transfer = Transfer::create([
                    'code' => 'TRF-' . strtoupper(Str::random(8)),
                    'origin_branch_id' => $originId,
                    'destination_branch_id' => $data['destination_branch_id'],
                    'created_by' => $user->id,
                    'status' => 'in_transit',
                    'notes' => $data['notes'] ?? null,
                    'shipped_at' => now()
                ]);

                // Procesar Items (FIFO)
                foreach ($data['items'] as $item) {
                    $qtyNeeded = $item['quantity'];
                    $totalCost = 0;
                    
                    // Buscar lotes
                    $lots = InventoryLot::where('branch_id', $originId)
                        ->where('sku_id', $item['sku_id'])
                        ->whereRaw('(quantity - reserved_quantity) > 0')
                        ->orderBy('expiration_date', 'asc')
                        ->lockForUpdate()
                        ->get();

                    // Validar Stock Total
                    $currentStock = $lots->sum(fn($l) => $l->quantity - $l->reserved_quantity);
                    if ($currentStock < $qtyNeeded) {
                        throw new \Exception("Stock insuficiente para el SKU ID {$item['sku_id']}. Tienes {$currentStock}, necesitas {$qtyNeeded}.");
                    }

                    foreach ($lots as $lot) {
                        if ($qtyNeeded <= 0) break;
                        
                        $available = $lot->quantity - $lot->reserved_quantity;
                        $take = min($available, $qtyNeeded);
                        
                        // Descontar
                        $lot->decrement('quantity', $take);
                        
                        InventoryMovement::create([
                            'branch_id' => $originId,
                            'sku_id' => $item['sku_id'],
                            'inventory_lot_id' => $lot->id,
                            'user_id' => $user->id,
                            'type' => 'transfer_out',
                            'quantity' => -$take,
                            'unit_cost' => $lot->unit_cost,
                            'reference' => 'Envío ' . $transfer->code
                        ]);

                        $totalCost += ($take * $lot->unit_cost);
                        $qtyNeeded -= $take;
                    }

                    // Guardar Item Transferencia
                    TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'sku_id' => $item['sku_id'],
                        'qty_sent' => $item['quantity'],
                        'unit_cost' => $item['quantity'] > 0 ? ($totalCost / $item['quantity']) : 0
                    ]);
                }
            });

            return redirect()->route('admin.transfers.index')->with('message', 'Transferencia enviada correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Nota: Cambiamos "Transfer $transfer" por "$id" para poder aplicar el bloqueo manual
    public function receive(ReceiveTransferRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                
                // 1. BLOQUEO PESIMISTA (La clave para evitar doble clic)
                // Buscamos la transferencia y "congelamos" la fila hasta terminar la transacción
                $transfer = Transfer::where('id', $id)->lockForUpdate()->firstOrFail();

                // 2. Validación de Estado (Ahora es segura porque tenemos el bloqueo)
                if ($transfer->status !== 'in_transit') {
                    // Si ya estaba lista, redirigimos al index sin lanzar error
                    return redirect()->route('admin.transfers.index')
                        ->with('message', 'Esta transferencia ya había sido procesada anteriormente.');
                }

                $data = $request->validated();

                // 3. Procesamiento de Ítems
                foreach ($data['items'] as $receivedItem) {
                    $transferItem = TransferItem::findOrFail($receivedItem['id']);
                    $qtyReceived = $receivedItem['qty_received'];
                    
                    // Calculamos la diferencia (Lo que se perdió/no llegó)
                    // qty_sent (10) - qty_received (8) = diff (2)
                    $diff = $transferItem->qty_sent - $qtyReceived;

                    // A. INGRESAR LO RECIBIDO EN DESTINO
                    if ($qtyReceived > 0) {
                        $newLot = InventoryLot::create([
                        'branch_id' => $transfer->destination_branch_id,
                        'sku_id' => $transferItem->sku_id,
                        'transfer_id' => $transfer->id, // <--- AHORA VINCULAMOS
                        'purchase_id' => null,           // <--- EXPLÍCITAMENTE NULL
                        'lot_code' => 'TRF-IN-' . Str::upper(Str::random(8)),
                        'quantity' => $qtyReceived,
                        'initial_quantity' => $qtyReceived,
                        'unit_cost' => $transferItem->unit_cost,
                        ]);
                        
                        InventoryMovement::create([
                            'branch_id' => $transfer->destination_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'inventory_lot_id' => $newLot->id,
                            'user_id' => $request->user()->id,
                            'type' => 'transfer_in',
                            'quantity' => $qtyReceived, // Positivo (Entrada)
                            'unit_cost' => $transferItem->unit_cost,
                            'reference' => 'Recepción Guía #' . $transfer->code
                        ]);
                    }

                    // B. DEVOLVER LA DIFERENCIA A ORIGEN (Retorno Automático)
                    if ($diff > 0) {
                        $returnLot = InventoryLot::create([
                        'branch_id' => $transfer->origin_branch_id,
                        'sku_id' => $transferItem->sku_id,
                        'transfer_id' => $transfer->id, // <--- AHORA VINCULAMOS
                        'purchase_id' => null,           // <--- EXPLÍCITAMENTE NULL
                        'lot_code' => 'RET-' . $transfer->code . '-' . Str::upper(Str::random(4)),
                        'quantity' => $diff,
                        'initial_quantity' => $diff,
                        'unit_cost' => $transferItem->unit_cost,
                        ]);
                        
                        InventoryMovement::create([
                            'branch_id' => $transfer->origin_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'inventory_lot_id' => $returnLot->id,
                            'user_id' => $request->user()->id,
                            'type' => 'transfer_return',
                            'quantity' => $diff, // Positivo (Reingreso a origen)
                            'unit_cost' => $transferItem->unit_cost,
                            'reference' => 'Devolución Auto (Faltante) Guía #' . $transfer->code
                        ]);
                    }

                    // Actualizamos el ítem con lo que realmente se declaró
                    $transferItem->update(['qty_received' => $qtyReceived]);
                }

                // 4. Cerrar la Transferencia
                $transfer->update([
                    'status' => 'completed',
                    'received_by' => $request->user()->id,
                    'received_at' => now()
                ]);

                return redirect()->route('admin.transfers.index')->with('message', 'Recepción procesada correctamente.');
            });

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }
}