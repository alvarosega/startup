<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Actions\Driver\Dashboard\GetDriverDashboardDataAction;
use App\Actions\Driver\Profile\ToggleDriverStatusAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== 'approved') {
            return redirect()->route('driver.profile.index');
        }

        // Buscar si tiene UNA orden activa en cualquier fase de la misión
        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['preparing', 'ready_for_dispatch', 'dispatched', 'arrived'])
            ->first();

        if ($activeOrder) {
            // Lo redirigimos a la vista de la orden. El OrderController se encargará de mostrar Recogida o Entrega.
            return redirect()->route('driver.orders.show', $activeOrder->code);
        }

        // Si no tiene misión activa, lo mandamos a pescar a la bolsa.
        return redirect()->route('driver.orders.index')
            ->with('error', 'No tienes ninguna misión en curso. Acepta una carga de la bolsa.');
    }

    public function history(): Response|RedirectResponse
    {
        $driver = Auth::guard('driver')->user();
        if ($driver->status !== 'approved') return redirect()->route('driver.profile.index');

        return Inertia::render('Driver/History');
    }

    public function toggleStatus(Request $request, ToggleDriverStatusAction $action): RedirectResponse
    {
        $action->execute(Auth::guard('driver')->user(), $request->boolean('is_online'));
        return redirect()->back();
    }
}