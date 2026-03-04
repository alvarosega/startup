<?php

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Order\CheckoutRequest;
use App\Actions\Customer\Order\CheckoutCartAction;
use App\Actions\Customer\Order\GetCheckoutDataAction;
use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Services\ShopContextService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(protected ShopContextService $contextService) {}

    public function index(GetCheckoutDataAction $action): Response|RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = auth()->guard('customer')->id();

            // Delegamos toda la carga cognitiva al Action
            $data = $action->execute($customerId, $branchId);
            
            return Inertia::render('Customer/Shop/Checkout/Index', $data);

        } catch (Exception $e) {
            return redirect()->route('customer.shop.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(CheckoutRequest $request, CheckoutCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = auth()->guard('customer')->id();

        Log::channel('single')->info('--- NUEVA ORDEN DE CHECKOUT ---');
        Log::channel('single')->info('Datos Validados:', $request->validated());
        
        $dto = CheckoutCartDTO::fromRequest($request, $branchId, $customerId);
        
        try {
            $order = $action->execute($dto);
            Log::channel('single')->info('Orden guardada: ' . $order->id);
            
            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Orden reservada. Tienes 10 minutos para subir tu comprobante de pago.');

        } catch (Exception $e) {
            Log::channel('single')->error('ERROR FATAL EN ACCIÓN:', ['msg' => $e->getMessage()]);
            return back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }
}