<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Muestra el Tablero Kanban
     */
    public function index()
    {
        // Obtenemos todas las órdenes activas agrupadas por estado
        // Excluimos 'cancelled' y 'completed' del tablero principal para no saturarlo,
        // o las cargamos limitadas.
        
        $orders = Order::with(['user', 'items'])
            ->whereIn('status', ['pending_proof', 'review', 'confirmed', 'dispatched'])
            ->orderBy('created_at', 'asc') // FIFO (First In First Out)
            ->get()
            ->groupBy('status');

        return Inertia::render('Admin/Orders/Kanban', [
            'orders' => $orders
        ]);
    }

    /**
     * Mover tarjeta en el Kanban (Actualizar Estado)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending_proof,review,confirmed,dispatched,completed,cancelled',
            'rejection_reason' => 'required_if:status,cancelled'
        ]);

        // Lógica de negocio al cambiar estado
        if ($request->status === 'cancelled') {
            // Devolver stock (Logica pendiente en InventoryController)
            // InventoryController::releaseStock($order);
        }

        $order->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason ?? null
        ]);

        return back()->with('success', "Orden #{$order->code} movida a {$request->status}");
    }
}