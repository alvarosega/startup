<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Requests\Customer\Order\TransitionToPaymentPendingRequest;
use App\DTOs\Customer\Order\TransitionToPaymentPendingDTO;
use App\Actions\Customer\Order\TransitionToPaymentPendingAction;
use App\Http\Resources\Customer\Order\OrderPendingResource;
use App\Http\Resources\Customer\Order\OrderIndexResource;
use App\Actions\Customer\Order\GetOrderTrackingDataAction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class OrderController extends Controller
{
    public function index(): Response
    {
        $customerId = (string) auth()->guard('customer')->id();

        $orders = Order::where('customer_id', $customerId)
            ->select(['id', 'code', 'status', 'delivery_type', 'total_amount', 'created_at']) // QUERY LAW
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(15);

        return Inertia::render('Customer/Order/Index', [
            'orders' => OrderIndexResource::collection($orders)
        ]);
    }

    /**
     * RECTIFICACIÓN: Enmascaramiento. Recibe $order resuelto por 'code'
     */
    public function show(Order $order): Response|RedirectResponse
    {
        // Validación de propiedad (Seguridad Horizontal)
        $customerId = (string) auth()->guard('customer')->id();
        if ($order->customer_id !== $customerId) {
            abort(403, 'Acceso denegado a esta orden.');
        }

        return match ($order->status) {
            'pending' => $this->renderPendingPayment($order),
            
            'payment_pending' => Inertia::render('Customer/Order/PaymentWaiting', [
                'order' => [
                    'id'           => $order->id,
                    'code'         => $order->code,
                    'total_amount' => (float) $order->total_amount,
                    'status'       => $order->status
                ]
            ]),
            'preparing', 'ready_for_dispatch', 'dispatched', 'arrived' => Inertia::render(
                'Customer/Order/Tracking', 
                app(GetOrderTrackingDataAction::class)->execute($order->id)
            ),
            
            default => redirect()->route('customer.order.index')
                        ->with('info', "La vista de detalle para el estado '{$order->status}' está en desarrollo.")
        };
    }

    /**
     * RECTIFICACIÓN: Enmascaramiento. Recibe $order resuelto por 'code'
     */
    public function uploadProof(
        TransitionToPaymentPendingRequest $request, 
        Order $order, // <--- CAMBIO AQUÍ
        TransitionToPaymentPendingAction $action
    ): RedirectResponse {
        try {
            $customerId = (string) auth()->guard('customer')->id();
            if ($order->customer_id !== $customerId) {
                throw new Exception("Acceso denegado a esta orden.");
            }

            $dto = new TransitionToPaymentPendingDTO(
                orderId: $order->id, // Pasamos el ID real al Action para integridad DB
                customerId: $customerId,
                proofFile: $request->file('proof')
            );

            $action->execute($dto);

            // Redirigimos usando el CODE
            return redirect()->route('customer.order.show', $order->code)
                ->with('success', 'Comprobante recibido. En proceso de validación táctica.');

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function renderPendingPayment(Order $order): Response|RedirectResponse
    {
        if ($order->reservation_expires_at < now()) {
            return redirect()->route('customer.order.index')
                ->with('error', 'El tiempo de reserva ha finalizado. La orden será cancelada.');
        }

        $resource = (new OrderPendingResource($order))->resolve();

        return Inertia::render('Customer/Order/PendingPayment', [
            'order'           => $resource['order'],
            'payment_context' => $resource['payment_context']
        ]);
    }
}