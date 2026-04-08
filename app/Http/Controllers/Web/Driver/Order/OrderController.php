<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Actions\Driver\Order\{
    GetAvailableOrdersAction, 
    GetOrderTrackingDataAction, 
    AcceptOrderAction, 
    VerifyPickupAction
};
use App\Http\Requests\Driver\Order\{AcceptOrderRequest, VerifyPickupRequest};
use Illuminate\Http\RedirectResponse;
use Inertia\{Inertia, Response};
use Exception;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Lista pedidos en 'preparing' y 'ready_for_dispatch' sin conductor.
     */
    public function index(GetAvailableOrdersAction $action): Response
    {
        return Inertia::render('Driver/Orders/Index', [
            'orders' => $action->execute()
        ]);
    }

    public function show(Order $order, GetOrderTrackingDataAction $action): Response|RedirectResponse
    {
        $driverId = (string) Auth::id();

        if ($order->driver_id && $order->driver_id !== $driverId) {
            return redirect()->route('driver.orders.index')->with('error', 'Pedido asignado a otro conductor.');
        }

        return match($order->status) {
            // VISTA 2: Preparación y Validación de Recogida (PIN del Admin)
            'preparing', 'ready_for_dispatch' => Inertia::render('Driver/Orders/ReadyForDispatch', $action->execute($order->id)),

            // VISTA 3: Ruta de Entrega y Validación de Cliente (PIN del Cliente)
            'dispatched' => Inertia::render('Driver/Orders/Delivery', $action->execute($order->id)),
            
            'arrived' => redirect()->route('driver.dashboard'), // Estado de "Llegué a la puerta"
            
            default => redirect()->route('driver.orders.index')->with('error', 'Pedido finalizado o no disponible.')
        };
    }

    /**
     * Paso 1: El driver "reserva" el pedido (early assignment).
     */
    public function take(AcceptOrderRequest $request, Order $order, AcceptOrderAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, (string) Auth::id());
            return redirect()->route('driver.orders.show', $order->code)
                ->with('success', 'Pedido asignado. Dirígete a la sucursal.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Paso 2: El driver ingresa el OTP dictado por el Admin para iniciar el viaje.
     */
    public function verifyPickup(VerifyPickupRequest $request, Order $order, VerifyPickupAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, $request->validated('pickup_otp'));
            return redirect()->route('driver.dashboard')
                ->with('success', 'Carga verificada. Inicia la ruta de entrega.');
        } catch (Exception $e) {
            return back()->withErrors(['pickup_otp' => $e->getMessage()]);
        }
    }
}