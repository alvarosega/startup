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
use App\Actions\Driver\Order\MarkOrderAsArrivedAction;
use App\Actions\Driver\Order\DeliverOrderAction;
use App\Http\Requests\Driver\Order\DeliverOrderRequest;
use App\Http\Requests\Driver\Order\{AcceptOrderRequest, VerifyPickupRequest};
use Illuminate\Http\RedirectResponse;
use Inertia\{Inertia, Response};
use Exception;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

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

            // RECTIFICACIÓN CRÍTICA: 'arrived' se une a 'dispatched' para renderizar la misma vista (Delivery.vue)
            'dispatched', 'arrived' => Inertia::render('Driver/Orders/Delivery', $action->execute($order->id)),
            
            default => redirect()->route('driver.orders.index')->with('error', 'Pedido finalizado o no disponible.')
        };
    }


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

    public function verifyPickup(VerifyPickupRequest $request, Order $order, VerifyPickupAction $action): RedirectResponse
    {
        try {
            $action->execute(
                $order->id, 
                (string) Auth::id(), 
                // RECTIFICACIÓN: Casteo explícito a string para cumplir con el contrato del Action
                (string) $request->validated('pickup_otp') 
            );
            
            return redirect()->route('driver.dashboard')
                ->with('success', 'Carga verificada. Inicia la ruta de entrega.');
        } catch (Exception $e) {
            return back()->withErrors(['pickup_otp' => $e->getMessage()]);
        }
    }
    public function markArrived(Order $order, MarkOrderAsArrivedAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, (string) Auth::id());
            return back()->with('success', 'Llegada registrada. Solicite el PIN al cliente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function deliver(DeliverOrderRequest $request, Order $order, DeliverOrderAction $action): RedirectResponse
    {
        try {
            $action->execute(
                $order->id, 
                (string) Auth::id(), 
                (string) $request->validated('delivery_otp')
            );
            return redirect()->route('driver.dashboard')
                ->with('success', 'Misión Cumplida. Carga entregada exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['delivery_otp' => $e->getMessage()]);
        }
    }
}