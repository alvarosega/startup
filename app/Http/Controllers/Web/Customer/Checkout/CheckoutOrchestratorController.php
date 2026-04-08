<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Checkout\PlaceOrderRequest;
use App\Actions\Customer\Checkout\PlaceOrderAction;
use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Services\Order\OrderFinancialService;
use App\Services\ShopContextService;
use App\DTOs\Customer\Checkout\CheckoutCartDTO;
use App\Models\CheckoutSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Uid\Uuid;
use Exception;

class CheckoutOrchestratorController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly OrderFinancialService $financialService,
        private readonly GetCustomerCartAction $getCartAction
    ) {}

    public function index(): Response|RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customer = auth()->guard('customer')->user();
        
        $cartData = $this->getCartAction->execute(null, (string) $customer->id, $branchId);

        if (empty($cartData['items'])) {
            return redirect()->route('customer.cart.index')->with('error', 'El carrito está vacío.');
        }

        // 1. Cálculo Logístico Oficial en RAM
        $subtotal = (float) $cartData['total_price'];
        $pickupLogistics = $this->financialService->calculate(app('App\Models\Branch')->find($branchId), $customer, $subtotal, 'pickup');
        $deliveryLogistics = $this->financialService->calculate(app('App\Models\Branch')->find($branchId), $customer, $subtotal, 'delivery');

        // RECTIFICACIÓN: Importación limpia y snapshot de ubicación incluido por integridad financiera
        DB::transaction(function () use ($customer, $branchId, $cartData, $pickupLogistics, $deliveryLogistics) {
            CheckoutSnapshot::where('customer_id', $customer->id)->where('branch_id', $branchId)->delete();
            // RECTIFICACIÓN: Pase el ARRAY directamente, NO el JSON string.
            CheckoutSnapshot::create([
                'id' => (string) Uuid::v7(),
                'cart_id' => $cartData['id'],
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'logistics_data' => [ // <--- ELIMINADO json_encode
                    'pickup' => $pickupLogistics,
                    'delivery' => $deliveryLogistics,
                    'location_snapshot' => [
                        'lat' => $customer->latitude,
                        'lng' => $customer->longitude
                    ]
                ],
                'expires_at' => now()->addMinutes(15)
            ]);
        });
        return Inertia::render('Customer/Checkout/Index', [
            'cart' => $cartData,
            'pickup_logistics' => $pickupLogistics,
            'delivery_logistics' => $deliveryLogistics,
            'customer_location' => [
                'lat' => $customer->latitude,
                'lng' => $customer->longitude,
            ]
        ]);
    }

    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = (string) auth()->guard('customer')->id();

            $dto = CheckoutCartDTO::fromRequest($request, $customerId, $branchId);
            
            // La validación de Idempotencia ya fue procesada por el Middleware CheckIdempotency
            $order = $action->execute($dto);

            return redirect()->route('customer.order.show', $order->id)
                ->with('success', 'Pedido reservado de forma segura.');

        } catch (Exception $e) {
            
            return back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }
}