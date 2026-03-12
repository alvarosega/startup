<?php

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

    /**
     * Muestra la proforma de pago con cálculos de logística en tiempo real.
     */
    public function index(GetCheckoutDataAction $action): Response|RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = auth()->guard('customer')->id();

            $data = $action->execute($customerId, $branchId);
            
            return Inertia::render('Customer/Shop/Checkout/Index', $data);

        } catch (Exception $e) {
            return redirect()->route('customer.cart.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Genera la orden, reserva stock y congela precios (Atomic Checkout).
     */
    public function store(CheckoutRequest $request, PlaceOrderAction $action): RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = auth()->guard('customer')->id();

            // Transformación de Request a Objeto de Negocio (DTO)
            $dto = CheckoutCartDTO::fromRequest($request, $customerId, $branchId);
            
            // Ejecución de la Acción Maestra
            $order = $action->execute($dto);

            Log::channel('single')->info("Orden Creada: {$order->code} por Cliente: {$customerId}");

            // Redirigimos al detalle para que el usuario suba su comprobante QR
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