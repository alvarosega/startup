<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\InventoryLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Muestra el Tablero Kanban
     */
    public function index()
    {
        // Traemos órdenes activas + las canceladas/completadas recientes (últimas 24h)
        // para que el tablero no se llene de basura histórica.
        $orders = Order::with(['user.profile', 'items.sku.product'])
            ->where(function($q) {
                $q->whereIn('status', ['pending_proof', 'review', 'confirmed', 'dispatched'])
                  ->orWhere('updated_at', '>=', now()->subDay());
            })
            ->orderBy('created_at', 'asc') // FIFO
            ->get()
            ->groupBy('status');

        return Inertia::render('Admin/Orders/Kanban', [
            'orders' => $orders
        ]);
    }

    /**
     * El Cerebro: Mover tarjeta y afectar Inventario/Caja
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        $request->validate([
            'status' => 'required|in:pending_proof,review,confirmed,dispatched,completed,cancelled',
            'rejection_reason' => 'required_if:status,cancelled'
        ]);

        try {
            DB::transaction(function () use ($order, $oldStatus, $newStatus, $request) {
                
                // LÓGICA DE INVENTARIO
                
                // CASO 1: APROBAR PAGO (Review -> Confirmed)
                // El dinero entró. Convertimos la "Reserva" en "Venta Real" (Bajamos el stock físico)
                if ($oldStatus === 'review' && $newStatus === 'confirmed') {
                    $this->finalizeStockDeduction($order);
                }

                // CASO 2: RECHAZAR/CANCELAR (Cualquier estado -> Cancelled)
                // Si la orden estaba en reserva (pending/review), liberamos la reserva.
                // Si ya estaba confirmada, sería una devolución (no cubierto aquí por simplicidad, asumimos rechazo de pago).
                if (in_array($oldStatus, ['pending_proof', 'review']) && $newStatus === 'cancelled') {
                    $this->releaseReservation($order);
                }

                // Actualizar Orden
                $order->update([
                    'status' => $newStatus,
                    'rejection_reason' => $newStatus === 'cancelled' ? $request->rejection_reason : null
                ]);
            });

            return back()->with('success', "Orden #{$order->code} actualizada a " . strtoupper($newStatus));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error lógico: ' . $e->getMessage()]);
        }
    }

    /**
     * Auxiliar: Convierte Reserva en Venta (Resta quantity y reserved_quantity)
     */
    private function finalizeStockDeduction(Order $order)
    {
        foreach ($order->items as $item) {
            $qtyToDeduct = $item->quantity;

            // Buscamos lotes con reserva activa para este SKU
            $lots = InventoryLot::where('branch_id', $order->branch_id)
                ->where('sku_id', $item->sku_id)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($lots as $lot) {
                if ($qtyToDeduct <= 0) break;

                // Cuánto tomamos de este lote (lo que tenga reservado o lo que necesitemos)
                $amount = min($lot->reserved_quantity, $qtyToDeduct);

                // RESTA REAL: Bajamos el físico y la reserva
                $lot->decrement('quantity', $amount);
                $lot->decrement('reserved_quantity', $amount);

                $qtyToDeduct -= $amount;
            }
        }
    }

    /**
     * Auxiliar: Libera la reserva (Resta reserved_quantity, mantiene quantity)
     */
    private function releaseReservation(Order $order)
    {
        foreach ($order->items as $item) {
            $qtyToRelease = $item->quantity;

            $lots = InventoryLot::where('branch_id', $order->branch_id)
                ->where('sku_id', $item->sku_id)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($lots as $lot) {
                if ($qtyToRelease <= 0) break;

                $amount = min($lot->reserved_quantity, $qtyToRelease);
                $lot->decrement('reserved_quantity', $amount);
                
                $qtyToRelease -= $amount;
            }
        }
    }
}