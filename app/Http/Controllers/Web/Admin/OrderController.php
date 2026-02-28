<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

// NUEVOS NAMESPACES (Dominio Order)
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\DTOs\Order\UpdateOrderStatusDTO;
use App\Actions\Order\UpdateOrderStatusAction;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user.profile', 'items.sku.product'])
            ->where(function($q) {
                $q->whereIn('status', ['pending_proof', 'review', 'confirmed', 'dispatched'])
                  ->orWhere('updated_at', '>=', now()->subDay());
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('status');

        return Inertia::render('Admin/Orders/Kanban', [
            'orders' => $orders
        ]);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request, 
        string $id, 
        UpdateOrderStatusAction $action
    ) {
        try {
            $order = Order::findOrFail($id);

            $dto = UpdateOrderStatusDTO::fromRequest($request, $order);

            $updatedOrder = $action->execute($dto);

            return back()->with(
                'success', 
                "Orden #{$updatedOrder->code} actualizada a " . strtoupper($updatedOrder->status)
            );

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar la orden: ' . $e->getMessage()]);
        }
    }
}