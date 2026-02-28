<?php
namespace App\Http\Controllers\Web\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\{ApprovePaymentRequest, RejectPaymentRequest};
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use App\Actions\Admin\Order\{GetAdminOrdersAction, ApproveOrderPaymentAction, RejectOrderPaymentAction, DispatchOrderAction};
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

class OrderController extends Controller
{
    public function index(GetAdminOrdersAction $action): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $action->execute()
        ]);
    }

    public function approvePayment(ApprovePaymentRequest $request, string $id, ApproveOrderPaymentAction $action): RedirectResponse
    {
        try {
            $dto = new ReviewPaymentDTO(
                orderId: $id,
                type: $request->validated('type'),
                bankReference: $request->validated('bank_reference')
            );
            $action->execute($dto);
            return back()->with('success', 'Pago aprobado exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectPayment(RejectPaymentRequest $request, string $id, RejectOrderPaymentAction $action): RedirectResponse
    {
        try {
            $dto = new ReviewPaymentDTO(
                orderId: $id,
                type: $request->validated('type'),
                rejectionReason: $request->validated('rejection_reason')
            );
            $action->execute($dto);
            return back()->with('success', 'Comprobante rechazado. El cliente ha sido notificado.');
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