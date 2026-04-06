<?php

namespace App\Http\Controllers\Web\Driver\Order;

use App\Http\Controllers\Controller;
use App\Models\{Order, Driver};
use App\Actions\Driver\Order\TakeOrderWithOtpAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $driver = Auth::guard('driver')->user();
        $driver->load('profile');

        // REGLA 1: Si ya tiene un pedido activo, lo mandamos al Dashboard (Ruta) directamente
        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['dispatched', 'arrived'])
            ->exists();

        if ($activeOrder) {
            return redirect()->route('driver.dashboard');
        }

        // REGLA 2: Solo ve pedidos 'ready_for_dispatch' de su sucursal y sin conductor asignado
        $availableOrders = Order::where('branch_id', $driver->branch_id)
            ->where('status', 'ready_for_dispatch')
            ->whereNull('driver_id')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'created_at']);

        return Inertia::render('Driver/Orders/Index', [
            'driver' => [
                'id' => $driver->id,
                'is_online' => (bool) $driver->is_online,
                'status' => $driver->status,
                'branch_id' => $driver->branch_id
            ],
            'availableOrders' => $availableOrders
        ]);
    }

    public function take(Request $request, string $id, TakeOrderWithOtpAction $action)
    {
        $request->validate(['otp' => ['required', 'string', 'size:5']]);
        
        $driver = Auth::guard('driver')->user();

        try {
            $action->execute($id, $driver, $request->otp);
            
            // REGLA: Inmediatamente a la vista de viaje
            return redirect()->route('driver.dashboard')
                ->with('success', 'Pedido verificado. Inicie la ruta.');
                
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }
}