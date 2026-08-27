<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver;

use App\Enums\Users\DriverStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        /** @var \App\Models\Users\Driver $driver */
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== DriverStatus::APPROVED) {
            return redirect()->route('driver.profile.index');
        }

        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['preparing', 'ready_for_dispatch', 'dispatched', 'arrived'])
            ->first();

        if ($activeOrder) {
            return redirect()->route('driver.orders.show', $activeOrder->code);
        }

        return redirect()->route('driver.orders.index')
            ->with('error', 'No tienes ninguna misión en curso. Acepta una carga de la bolsa.');
    }

    public function history(): Response|RedirectResponse
    {
        /** @var \App\Models\Users\Driver $driver */
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== DriverStatus::APPROVED) {
            return redirect()->route('driver.profile.index');
        }

        return Inertia::render('Driver/History');
    }

    public function toggleStatus(): RedirectResponse
    {
        /** @var \App\Models\Users\Driver $driver */
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== DriverStatus::APPROVED) {
            return back()->with('error', 'Tu cuenta debe estar aprobada para operar.');
        }

        if (empty($driver->branch_id)) {
            return back()->with('error', 'No puedes ponerte en línea sin una sucursal asignada.');
        }

        $driver->update([
            'is_online'    => !$driver->is_online,
            'is_available' => !$driver->is_online,
        ]);

        return back()->with('success', 'Estado operativo actualizado.');
    }
}