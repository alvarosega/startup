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
}