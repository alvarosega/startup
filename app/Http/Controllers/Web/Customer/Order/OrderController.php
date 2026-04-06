<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Order\UploadOrderProofRequest;
use App\DTOs\Customer\Order\{GetCustomerOrderDTO, UploadOrderProofDTO};
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

use App\Actions\Customer\Order\GetCustomerOrdersAction;
use App\Actions\Customer\Order\UploadOrderProofAction;
use App\Actions\Customer\Order\States\{
    GetPendingPaymentDataAction,
    GetPaymentWaitDataAction,
    GetRejectionDataAction,
    GetStandardOrderDataAction,
    GetInRouteDataAction
};

class OrderController extends Controller
{
    public function index(GetCustomerOrdersAction $action): Response
    {
        $dto = new GetCustomerOrderDTO(customerId: (string) auth()->guard('customer')->id());
        
        return Inertia::render('Customer/Orders/Index', [
            'orders' => $action->execute($dto)
        ]);
    }

    public function show(string $id): Response
    {
        $customerId = (string) auth()->guard('customer')->id();
        $order = Order::where('customer_id', $customerId)->findOrFail($id, ['id', 'status']);
        $dto = new GetCustomerOrderDTO(customerId: $customerId, orderId: $id);

        return match($order->status) {
            'pending'         => Inertia::render('Customer/Orders/States/PendingPayment', app(GetPendingPaymentDataAction::class)->execute($dto)),
            'payment_pending' => Inertia::render('Customer/Orders/States/PaymentWait', app(GetPaymentWaitDataAction::class)->execute($dto)),
            'rejected'        => Inertia::render('Customer/Orders/States/PaymentRejected', app(GetRejectionDataAction::class)->execute($dto)),
            'dispatched', 'arrived' => Inertia::render('Customer/Orders/States/InRoute', app(GetInRouteDataAction::class)->execute($dto)),
            default           => Inertia::render('Customer/Orders/States/StandardView', app(GetStandardOrderDataAction::class)->execute($dto)),
        };
    }

    public function uploadProof(UploadOrderProofRequest $request, string $id, UploadOrderProofAction $action): RedirectResponse
    {
        try {
            $dto = UploadOrderProofDTO::fromRequest($request, $id, auth()->guard('customer')->id());
            $action->execute($dto);
            return back()->with('success', 'Comprobante recibido.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}