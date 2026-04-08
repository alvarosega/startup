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
     * FSM Router: Decide qué vista renderizar basándose matemáticamente en el estado de la orden.
     */
    public function show(string $id): Response|RedirectResponse
    {
        $customerId = (string) auth()->guard('customer')->id();

        $order = Order::where('id', $id)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        return match ($order->status) {
            'pending' => $this->renderPendingPayment($order),
            'payment_pending' => Inertia::render('Customer/Order/PaymentWaiting', ['code' => $order->code]),
            // Aquí se irán añadiendo los demás estados en las siguientes fases
            default => Inertia::render('Customer/Order/Show', ['order' => $order->toArray()]) 
        };
    }

    /**
     * Punto de entrada para la inyección del comprobante.
     */
    public function uploadProof(
        TransitionToPaymentPendingRequest $request, 
        string $id, 
        TransitionToPaymentPendingAction $action
    ): RedirectResponse {
        try {
            $dto = new TransitionToPaymentPendingDTO(
                orderId: $id,
                customerId: (string) auth()->guard('customer')->id(),
                proofFile: $request->file('proof')
            );

            $action->execute($dto);

            return redirect()->route('customer.order.show', $id)
                ->with('success', 'Comprobante recibido. En proceso de validación táctica.');

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Sub-rutina de renderizado aislada.
     */
    private function renderPendingPayment(Order $order): Response|RedirectResponse
    {
        // Si el estado es pending pero ya expiró el tiempo, se deniega la vista
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