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
    public function index(GetDriverDashboardDataAction $action): Response|RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== 'approved') {
            return redirect()->route('driver.profile.index');
        }

        $hasActiveOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['dispatched', 'arrived'])
            ->exists();

        if (!$hasActiveOrder) {
            return redirect()->route('driver.orders.index');
        }

        return Inertia::render('Driver/Dashboard', $action->execute($driver));
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