<?php

namespace App\Http\Controllers\Admin;

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

class TransferController extends Controller
{
    // --- VISTAS ---
    public function index()
    {
        $transfers = Transfer::with(['origin', 'destination', 'sender'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Inventory/Transfers/Index', ['transfers' => $transfers]);
    }

    public function create()
    {
        // Enviamos Inventario Real para que no seleccionen algo sin stock
        // (Reutiliza la lógica optimizada que hicimos en Transformaciones)
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

        return Inertia::render('Admin/Inventory/Transfers/Create', [
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'inventory' => $inventory
        ]);
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['origin', 'destination', 'sender', 'items.sku.product']);
        return Inertia::render('Admin/Inventory/Transfers/Show', ['transfer' => $transfer]);
    }

    // --- LOGICA DE ENVÍO (ORIGEN -> TRÁNSITO) ---
    public function store(StoreTransferRequest $request)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request) {
                // 1. Crear Cabecera
                $transfer = Transfer::create([
                    'origin_branch_id' => $data['origin_branch_id'],
                    'destination_branch_id' => $data['destination_branch_id'],
                    'created_by' => $request->user()->id,
                    'notes' => $data['notes'],
                    'status' => 'in_transit'
                ]);

                // 2. Procesar Items (FIFO)
                foreach ($data['items'] as $item) {
                    $qtyNeeded = $item['quantity'];
                    $totalCost = 0;
                    
                    // Buscar lotes en Origen (FIFO)
                    $lots = InventoryLot::where('branch_id', $data['origin_branch_id'])
                        ->where('sku_id', $item['sku_id'])
                        ->whereRaw('(quantity - reserved_quantity) > 0')
                        ->orderBy('expiration_date', 'asc')
                        ->get();

                    if ($lots->sum('quantity') < $qtyNeeded) {
                        throw new \Exception("Stock insuficiente para el SKU ID {$item['sku_id']}");
                    }

                    // Descontar Stock
                    foreach ($lots as $lot) {
                        if ($qtyNeeded <= 0) break;
                        
                        $available = $lot->quantity - $lot->reserved_quantity;
                        $take = min($available, $qtyNeeded);
                        
                        $lot->decrement('quantity', $take);
                        
                        InventoryMovement::create([
                            'branch_id' => $data['origin_branch_id'],
                            'sku_id' => $item['sku_id'],
                            'inventory_lot_id' => $lot->id,
                            'user_id' => $request->user()->id,
                            'type' => 'transfer_out',
                            'quantity' => -$take,
                            'unit_cost' => $lot->unit_cost,
                            'reference' => 'Envío TRF ' . $transfer->code
                        ]);

                        $totalCost += ($take * $lot->unit_cost);
                        $qtyNeeded -= $take;
                    }

                    // Calcular Costo Promedio Ponderado de este envío
                    // Si envié 5 de 10bs y 5 de 12bs, el costo unitario de transferencia es 11bs.
                    $avgCost = $totalCost / $item['quantity'];

                    TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'sku_id' => $item['sku_id'],
                        'qty_sent' => $item['quantity'],
                        'unit_cost' => $avgCost 
                    ]);
                }
            });

            return redirect()->route('admin.transfers.index')->with('message', 'Transferencia enviada correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // --- LOGICA DE RECEPCIÓN (TRÁNSITO -> DESTINO + DEVOLUCIÓN) ---
    public function receive(ReceiveTransferRequest $request, Transfer $transfer)
    {
        if ($transfer->status !== 'in_transit') {
            return back()->withErrors(['error' => 'Esta transferencia ya fue procesada.']);
        }

        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $transfer) {
                
                foreach ($data['items'] as $receivedItem) {
                    $transferItem = TransferItem::findOrFail($receivedItem['id']);
                    $qtyReceived = $receivedItem['qty_received'];
                    $qtySent = $transferItem->qty_sent;
                    $diff = $qtySent - $qtyReceived;

                    // 1. Ingresar lo Recibido en DESTINO (B)
                    if ($qtyReceived > 0) {
                        $newLotB = InventoryLot::create([
                            'branch_id' => $transfer->destination_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'lot_code' => 'TRF-IN-' . Str::random(6),
                            'quantity' => $qtyReceived,
                            'initial_quantity' => $qtyReceived,
                            'unit_cost' => $transferItem->unit_cost, // Mantiene costo histórico
                            'expiration_date' => null // Opcional: Podríamos haber pasado la fecha en el envío
                        ]);

                        InventoryMovement::create([
                            'branch_id' => $transfer->destination_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'inventory_lot_id' => $newLotB->id,
                            'user_id' => $request->user()->id,
                            'type' => 'transfer_in',
                            'quantity' => $qtyReceived,
                            'unit_cost' => $transferItem->unit_cost,
                            'reference' => 'Recepción TRF ' . $transfer->code
                        ]);
                    }

                    // 2. Devolver la Diferencia a ORIGEN (A)
                    if ($diff > 0) {
                        // Creamos un lote nuevo en A con la diferencia
                        $returnLotA = InventoryLot::create([
                            'branch_id' => $transfer->origin_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'lot_code' => 'RET-' . $transfer->code . '-' . Str::random(3),
                            'quantity' => $diff,
                            'initial_quantity' => $diff,
                            'unit_cost' => $transferItem->unit_cost,
                            'expiration_date' => null 
                        ]);

                        InventoryMovement::create([
                            'branch_id' => $transfer->origin_branch_id,
                            'sku_id' => $transferItem->sku_id,
                            'inventory_lot_id' => $returnLotA->id,
                            'user_id' => $request->user()->id,
                            'type' => 'transfer_return', // Tipo especial: Devolución
                            'quantity' => $diff,
                            'unit_cost' => $transferItem->unit_cost,
                            'reference' => 'Devolución TRF ' . $transfer->code . ' (No Recibido)'
                        ]);
                    }
                    
                    // Actualizar el item con lo que realmente llegó
                    $transferItem->update(['qty_received' => $qtyReceived]);
                }

                // Cerrar Transferencia
                $transfer->update([
                    'status' => 'completed',
                    'received_by' => $request->user()->id,
                    'received_at' => now()
                ]);
            });

            return redirect()->route('admin.transfers.index')->with('message', 'Recepción procesada. Diferencias devueltas a origen.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}