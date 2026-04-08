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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Actions\Admin\Order\GetReadyForDispatchDataAction; // <-- ESTA LÍNEA
use App\Http\Requests\Admin\Order\DispatchOrderRequest; // Añadir arriba
use App\Actions\Admin\Order\MarkOrderAsReadyAction;

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

    public function show(Order $order): Response
    {
        // El Order ya fue resuelto por Laravel usando el 'code' de la URL
        return match($order->status) {
            'payment_pending'    => Inertia::render('Admin/Orders/PaymentReview', app(GetPaymentReviewDataAction::class)->execute($order->id)),
            'preparing'          => Inertia::render('Admin/Orders/Preparation', app(GetPreparationDataAction::class)->execute($order->id)),
            'ready_for_dispatch' => Inertia::render('Admin/Orders/ReadyForDispatch', app(GetReadyForDispatchDataAction::class)->execute($order->id)),
            default              => Inertia::render('Admin/Orders/ReadOnly', app(GetReadOnlyOrderDataAction::class)->execute($order->id)),
        };
    }
    public function showProof(Order $order): StreamedResponse
    {
        // Verificamos si existe la referencia en DB y el archivo en disco
        if (!$order->proof_of_payment || !Storage::disk('local')->exists($order->proof_of_payment)) {
            abort(404, 'Comprobante físico no encontrado en el storage de seguridad.');
        }

        return Storage::disk('local')->response($order->proof_of_payment);
    }
    public function approvePayment(ApprovePaymentRequest $request, Order $order, ApproveOrderPaymentAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, $request->validated('bank_reference'));
            return redirect()->route('admin.orders.index')->with('success', 'Pago aprobado. Orden en preparación.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function rejectPayment(RejectPaymentRequest $request, Order $order, RejectOrderPaymentAction $action): RedirectResponse
    {
        try {
            $action->execute(
                $order->id, 
                $request->validated('rejection_reason'), 
                $request->validated('rejection_action')
            );
            
            $msg = $request->validated('rejection_action') === 'cancel' 
                ? 'Orden cancelada y stock liberado.' 
                : 'Comprobante rechazado. Se habilitó reintento al cliente.';
                
            return redirect()->route('admin.orders.index')->with('success', $msg);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function unassignDriver(Order $order, UnassignDriverAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id);
            return back()->with('success', 'Conductor removido. El pedido vuelve a estar disponible para la flota.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function markAsReady(Order $order, MarkOrderAsReadyAction $action): RedirectResponse
    {
        try {
            // Pasamos el ID real ($order->id) al Action para la operación en BD
            $action->execute($order->id);
            
            // Redirigimos al Index central de logística
            return redirect()->route('admin.orders.index')
                ->with('success', 'Orden empaquetada. PINs de seguridad logística generados.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function dispatchOrder(DispatchOrderRequest $request, Order $order, DispatchOrderAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id, $request->validated('pickup_otp'));
            return redirect()->route('admin.orders.index')->with('success', 'PIN verificado. Paquete liberado y en camino.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}