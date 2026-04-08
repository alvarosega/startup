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
            'ready_for_dispatch' => Inertia::render('Admin/Orders/Dispatch', app(GetDispatchDataAction::class)->execute($order->id)),
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

    public function markAsReady(Order $order): RedirectResponse
    {
        try {
            if ($order->status !== 'preparing') throw new Exception("La orden debe estar en preparación para marcarse como lista.");

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

    public function dispatchOrder(Order $order, DispatchOrderAction $action): RedirectResponse
    {
        try {
            $action->execute($order->id);
            return back()->with('success', 'Orden marcada como Despachada.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}