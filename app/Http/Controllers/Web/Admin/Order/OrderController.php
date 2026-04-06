<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\{ApprovePaymentRequest, RejectPaymentRequest};
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

use App\Actions\Admin\Order\{
    GetPaymentReviewDataAction,
    GetPreparationDataAction,
    GetDispatchDataAction,
    GetReadOnlyOrderDataAction,
    GetAdminOrdersAction, 
    ApproveOrderPaymentAction, 
    RejectOrderPaymentAction, 
    DispatchOrderAction
};

class OrderController extends Controller
{
    public function index(GetAdminOrdersAction $action): Response
    {
        return Inertia::render('Admin/Orders/Index', ['orders' => $action->execute()]);
    }

    public function show(string $id): Response
    {
        $order = Order::findOrFail($id, ['id', 'status']);

        return match($order->status) {
            'payment_pending'    => Inertia::render('Admin/Orders/PaymentReview', app(GetPaymentReviewDataAction::class)->execute($id)),
            'preparing'          => Inertia::render('Admin/Orders/Preparation', app(GetPreparationDataAction::class)->execute($id)),
            'ready_for_dispatch' => Inertia::render('Admin/Orders/Dispatch', app(GetDispatchDataAction::class)->execute($id)),
            default              => Inertia::render('Admin/Orders/ReadOnly', app(GetReadOnlyOrderDataAction::class)->execute($id)),
        };
    }

    public function approvePayment(ApprovePaymentRequest $request, string $id, ApproveOrderPaymentAction $action): RedirectResponse
    {
        try {
            $dto = new ReviewPaymentDTO(orderId: $id, bankReference: $request->validated('bank_reference'));
            $action->execute($dto);
            return back()->with('success', 'Pago aprobado. La orden pasó a Preparación.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectPayment(RejectPaymentRequest $request, string $id, RejectOrderPaymentAction $action): RedirectResponse
    {
        try {
            $dto = new ReviewPaymentDTO(orderId: $id, rejectionReason: $request->validated('rejection_reason'));
            $action->execute($dto);
            return back()->with('success', 'Comprobante rechazado. El stock vuelve a reserva temporal.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function markAsReady(string $id): RedirectResponse
    {
        try {
            $order = Order::findOrFail($id);
            if ($order->status !== 'preparing') throw new Exception("La orden debe estar en preparación para marcarse como lista.");

            // RECTIFICACIÓN CRÍTICA: Generación dual de OTP (Driver y Customer)
            $order->update([
                'status' => 'ready_for_dispatch',
                'pickup_otp'   => str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT),
                'delivery_otp' => str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT)
            ]);

            return back()->with('success', 'Orden empaquetada. PINs de recogida y entrega generados.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function dispatchOrder(string $id, DispatchOrderAction $action): RedirectResponse
    {
        try {
            $action->execute($id);
            return back()->with('success', 'Orden marcada como Despachada.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}