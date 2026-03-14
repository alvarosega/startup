<?php

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Order\UploadOrderProofRequest;
use App\DTOs\Customer\Order\{GetCustomerOrderDTO, UploadOrderProofDTO};
use App\Actions\Customer\Order\{GetCustomerOrdersAction, GetCustomerOrderDetailAction, UploadOrderProofAction};
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

class OrderController extends Controller
{
    public function index(GetCustomerOrdersAction $action): Response
    {
        $dto = new GetCustomerOrderDTO(auth()->guard('customer')->id());
        $orders = $action->execute($dto);

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders
        ]);
    }

    public function show(string $id, GetCustomerOrderDetailAction $action): Response
    {
        $dto = new GetCustomerOrderDTO(auth()->guard('customer')->id(), $id);
        $data = $action->execute($dto);

        return Inertia::render('Customer/Orders/Show', $data);
    }

    public function uploadProof(UploadOrderProofRequest $request, string $id, UploadOrderProofAction $action): RedirectResponse
    {
        try {
            $dto = UploadOrderProofDTO::fromRequest($request, $id, auth()->guard('customer')->id());
            $action->execute($dto);

            return back()->with('success', 'Comprobante recibido. Pasará a revisión administrativa.');
            
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function track(string $id): \Inertia\Response
    {
        $order = \App\Models\Order::where('customer_id', auth()->guard('customer')->id())
            ->where('id', $id)
            // CORREGIDO: Usamos 'profile' en lugar de 'details'
            ->with(['driver.profile', 'branch'])
            ->firstOrFail();

        $latestLocation = null;
        if ($order->driver_id) {
            $latestLocation = \App\Models\DriverLocationLog::where('driver_id', $order->driver_id)
                ->latest()
                ->first(['latitude', 'longitude']);
        }

        return Inertia::render('Customer/Orders/Track', [
            'order' => [
                'id' => $order->id,
                'code' => $order->code,
                'status' => $order->status,
                'delivery_data' => $order->delivery_data,
                // El cliente solo ve el código si el driver marcó llegada
                'delivery_otp' => $order->status === 'arrived' ? $order->delivery_otp : null,
                'driver' => $order->driver ? [
                    'profile' => $order->driver->profile,
                    'phone' => $order->driver->phone
                ] : null
            ],
            'initialDriverLocation' => $latestLocation
        ]);
    }

    public function getTelemetry(string $id)
    {
        $order = \App\Models\Order::where('customer_id', auth()->guard('customer')->id())
            ->where('id', $id)
            // Extraemos también el OTP para poder enviarlo
            ->firstOrFail(['driver_id', 'status', 'delivery_otp']);

        if (!$order->driver_id) {
            return response()->json(['active' => false]);
        }

        $location = \App\Models\DriverLocationLog::where('driver_id', $order->driver_id)
            ->latest()
            ->first(['latitude', 'longitude', 'created_at']);

        return response()->json([
            'active' => true,
            'status' => $order->status,
            'coords' => $location,
            // Solo enviamos el PIN si el conductor ya reportó llegada
            'otp' => $order->status === 'arrived' ? $order->delivery_otp : null
        ]);
    }
}