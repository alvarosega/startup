<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// DTOs
use App\DTOs\Driver\Order\CompleteOrderDTO;

// Actions
use App\Actions\Driver\Dashboard\GetDriverDashboardDataAction;
use App\Actions\Driver\Dashboard\GetPendingOrdersAction;
use App\Actions\Driver\Order\TakeOrderAction;
use App\Actions\Driver\Order\MarkOrderAsArrivedAction;
use App\Actions\Driver\Order\CompleteOrderAction; 
use App\Actions\Driver\Profile\ToggleDriverStatusAction;

class DashboardController extends Controller
{
    public function index(GetDriverDashboardDataAction $action)
    {
        $driver = Auth::guard('driver')->user();

        if ($driver->status !== 'active') {
            return redirect()->route('driver.profile.index')
                ->with('error', 'Acceso denegado. Sube tus documentos para operar.');
        }

        $data = $action->execute($driver);

        return Inertia::render('Driver/Dashboard', $data);
    }

    public function history()
    {
        $driver = Auth::guard('driver')->user();
        
        if ($driver->status !== 'active') {
            return redirect()->route('driver.profile.index');
        }

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
        if (!$driver || !$driver->branch_id) {
            return response()->json([]);
        }

        return response()->json($action->execute($driver->branch_id));
    }

    public function takeOrder(string $id, TakeOrderAction $action)
    {
        try {
            $action->execute($id, Auth::guard('driver')->user());
            return redirect()->route('driver.dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['order_conflict' => $e->getMessage()]);
        }
    }

    public function markAsArrived(string $id, MarkOrderAsArrivedAction $action)
    {
        try {
            $action->execute($id, Auth::guard('driver')->id());
            return back()->with('success', 'Llegada notificada. Solicita el PIN al cliente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function completeOrder(Request $request, string $id, CompleteOrderAction $action)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:4']
        ]);

        try {
            $dto = new CompleteOrderDTO($id, Auth::guard('driver')->id(), $request->otp);
            $action->execute($dto);
            
            return redirect()->route('driver.dashboard')->with('success', 'Entrega confirmada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }
}