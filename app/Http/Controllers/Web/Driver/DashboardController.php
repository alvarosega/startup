<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\DTOs\Driver\Order\CompleteOrderDTO;
use App\Actions\Driver\Dashboard\{GetDriverDashboardDataAction, GetPendingOrdersAction};
use App\Actions\Driver\Order\{TakeOrderAction, VerifyPickupAction, MarkOrderAsArrivedAction, CompleteOrderAction};
use App\Actions\Driver\Profile\ToggleDriverStatusAction;

class DashboardController extends Controller
{
    public function index(GetDriverDashboardDataAction $action)
    {
        $driver = Auth::guard('driver')->user();

        // Si no tiene una orden activa, no tiene nada que hacer aquí
        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['dispatched', 'arrived'])
            ->exists();

        if (!$activeOrder) {
            return redirect()->route('driver.orders.index');
        }

        return Inertia::render('Driver/Dashboard', $action->execute($driver));
    }

    public function history()
    {
        $driver = Auth::guard('driver')->user();
        // RECTIFICACIÓN: Cambiar 'active' por 'approved'
        if ($driver->status !== 'approved') return redirect()->route('driver.profile.index');

        return Inertia::render('Driver/History', [
            'driver' => [
                'id' => $driver->id,
                'status' => $driver->status 
            ]
        ]);
    }
    public function toggleStatus(Request $request, ToggleDriverStatusAction $action)
    {
        $isOnline = $request->boolean('is_online');
        $action->execute(Auth::guard('driver')->user(), $isOnline);
        return redirect()->back();
    }

    public function pollPendingOrders(GetPendingOrdersAction $action)
    {
        $driver = Auth::guard('driver')->user();
        if (!$driver || !$driver->branch_id) return response()->json([]);

        return response()->json($action->execute($driver->branch_id));
    }

    /**
     * FASE 2: Aceptar pedido (Estado: preparing)
     */
    public function takeOrder(string $id, TakeOrderAction $action)
    {
        try {
            $action->execute($id, Auth::guard('driver')->user());
            return back()->with('success', 'Pedido asignado. Ve a la sucursal.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * FASE 2: Validar recogida en tienda (Pickup OTP)
     * Mueve el estado de 'preparing' a 'dispatched'
     */
    public function verifyPickup(Request $request, string $id, VerifyPickupAction $action)
    {
        $request->validate([
            'pickup_otp' => ['required', 'string', 'size:5']
        ]);

        try {
            $action->execute($id, Auth::guard('driver')->id(), $request->pickup_otp);
            return back()->with('success', 'Recogida confirmada. ¡Inicia la ruta!');
        } catch (\Exception $e) {
            return back()->withErrors(['pickup_otp' => $e->getMessage()]);
        }
    }

    /**
     * FASE 3: Notificar llegada al cliente
     */
    public function markAsArrived(string $id, MarkOrderAsArrivedAction $action)
    {
        try {
            $action->execute($id, Auth::guard('driver')->id());
            return back()->with('success', 'Llegada notificada. Solicita el PIN al cliente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * FASE 3: Finalizar entrega (Delivery OTP)
     * Mueve el estado de 'arrived' a 'completed'
     */
    public function completeOrder(Request $request, string $id, CompleteOrderAction $action)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:4']
        ]);

        try {
            $dto = new CompleteOrderDTO($id, Auth::guard('driver')->id(), $request->otp);
            $action->execute($dto);
            
            return redirect()->route('driver.dashboard')->with('success', 'Entrega finalizada con éxito.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }
}