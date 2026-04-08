<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\{ApprovePaymentRequest, RejectPaymentRequest, DispatchOrderRequest};
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// ACTIONS LIMPIOS
use App\Actions\Admin\Order\{
    GetAdminOrdersAction,
    GetPaymentReviewDataAction,
    GetPreparationDataAction,
    GetReadyForDispatchDataAction,
    GetReadOnlyOrderDataAction,
    ApproveOrderPaymentAction,
    RejectOrderPaymentAction,
    MarkOrderAsReadyAction,
    UnassignDriverAction,
    DispatchOrderAction
};

class OrderController extends Controller
{
    public function index(GetAdminOrdersAction $action): Response
    {
        return Inertia::render('Admin/Orders/Index', ['orders' => $action->execute()]);
    }

    public function show(Order $order): Response
    {
        return match($order->status) {
            'payment_pending'    => Inertia::render('Admin/Orders/PaymentReview', app(GetPaymentReviewDataAction::class)->execute($order->id)),
            'preparing'          => Inertia::render('Admin/Orders/Preparation', app(GetPreparationDataAction::class)->execute($order->id)),
            'ready_for_dispatch' => Inertia::render('Admin/Orders/ReadyForDispatch', app(GetReadyForDispatchDataAction::class)->execute($order->id)),
            default              => Inertia::render('Admin/Orders/ReadOnly', app(GetReadOnlyOrderDataAction::class)->execute($order->id)),
        };
    }

    public function approvePayment(ApprovePaymentRequest $request, Order $order, ApproveOrderPaymentAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, $request->validated('bank_reference'));
            return redirect()->route('admin.orders.index')->with('success', 'Pago aprobado.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function markAsReady(Order $order, MarkOrderAsReadyAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id);
            return redirect()->route('admin.orders.index')->with('success', 'Orden lista para despacho.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function unassignDriver(Order $order, UnassignDriverAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id);
            return back()->with('success', 'Conductor desvinculado.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function dispatchOrder(DispatchOrderRequest $request, Order $order, DispatchOrderAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, $request->validated('pickup_otp'));
            return redirect()->route('admin.orders.index')->with('success', 'PIN verificado. Paquete en camino.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function showProof(Order $order): StreamedResponse
    {
        if (!$order->proof_of_payment || !Storage::disk('local')->exists($order->proof_of_payment)) {
            abort(404);
        }
        return Storage::disk('local')->response($order->proof_of_payment);
    }
}