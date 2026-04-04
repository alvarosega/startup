<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Order\CheckoutRequest;
use App\Actions\Customer\Order\{PlaceOrderAction, GetCheckoutDataAction};
use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Services\ShopContextService;
use Inertia\{Inertia, Response};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckoutController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function index(GetCheckoutDataAction $action): Response|RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = (string) auth()->guard('customer')->id();
            
            $guestUuid = request()->header('X-Guest-UUID') ?? session('guest_client_uuid');

            // RECTIFICACIÓN: Envío estricto de la firma de 3 parámetros
            $data = $action->execute($guestUuid, $customerId, $branchId);
            
            return Inertia::render('Customer/Shop/Checkout/Index', $data);

        } catch (Exception $e) {
            return redirect()->route('customer.cart.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(CheckoutRequest $request, PlaceOrderAction $action): RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = (string) auth()->guard('customer')->id();

            $dto = CheckoutCartDTO::fromRequest($request, $customerId, $branchId);
            
            $order = $action->execute($dto);

            Log::channel('single')->info("Orden Creada: {$order->code} por Cliente: {$customerId}");

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Pedido reservado. Tienes 10 minutos para completar el pago.');

        } catch (Exception $e) {
            Log::channel('single')->error("Fallo en Checkout: " . $e->getMessage());
            
            return back()->withErrors([
                'checkout_error' => 'No se pudo procesar el pedido: ' . $e->getMessage()
            ]);
        }
    }
}