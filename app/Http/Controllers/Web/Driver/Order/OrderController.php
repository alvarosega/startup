<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver\Order;

use App\Http\Controllers\Controller;
use App\Models\{Order, Driver};
use App\Actions\Driver\Order\TakeOrderWithOtpAction;
use App\Actions\Driver\Order\MarkOrderAsArrivedAction;
use App\Actions\Driver\Order\CompleteOrderAction;
use App\DTOs\Driver\Order\CompleteOrderDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== 'approved') {
            return redirect()->route('driver.profile.index');
        }

        // Bloqueo Monotarea
        $hasActiveOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['dispatched', 'arrived'])
            ->exists();

        if ($hasActiveOrder) {
            return redirect()->route('driver.dashboard');
        }

        $availableOrders = Order::where('branch_id', $driver->branch_id)
            ->where('status', 'ready_for_dispatch')
            ->whereNull('driver_id')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'created_at']);

        return Inertia::render('Driver/Orders/Index', [
            'driver' => [
                'id' => $driver->id,
                'is_online' => (bool) $driver->is_online,
            ],
            'availableOrders' => $availableOrders
        ]);
    }

    public function take(Request $request, string $id, TakeOrderWithOtpAction $action): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'string', 'size:5']]);
        
        try {
            $action->execute($id, Auth::guard('driver')->user(), $request->otp);
            return redirect()->route('driver.dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    public function markAsArrived(string $id, MarkOrderAsArrivedAction $action): RedirectResponse
    {
        try {
            $action->execute($id, Auth::guard('driver')->id());
            return back();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function completeOrder(Request $request, string $id, CompleteOrderAction $action): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'string', 'size:4']]);

        try {
            $dto = new CompleteOrderDTO($id, Auth::guard('driver')->id(), $request->otp);
            $action->execute($dto);
            return redirect()->route('driver.orders.index');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }
}