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
        
        // 1. Obtención de Cart (Sincrónico para validación inmediata)
        $cartData = $this->getCartAction->execute(null, (string) $customer->id, $branchId);

        if (empty($cartData['items'])) {
            return redirect()->route('customer.cart.index')->with('error', 'El carrito está vacío.');
        }

        $subtotal = (float) $cartData['total_price'];
        $branch = app('App\Models\Branch')->find($branchId);

        // 2. Snapshot de Integridad (Sincrónico - Garantiza el precio)
        // El snapshot se crea de inmediato para blindar la operación.
        DB::transaction(function () use ($customer, $branchId, $cartData) {
            CheckoutSnapshot::where('customer_id', $customer->id)->where('branch_id', $branchId)->delete();
            CheckoutSnapshot::create([
                'id' => (string) \Symfony\Component\Uid\Uuid::v7(),
                'cart_id' => $cartData['id'],
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'logistics_data' => [
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
            
            // DEFER: Cálculos financieros y logísticos (Activan Skeletons)
            'pickup_logistics' => Inertia::defer(fn() => 
                $this->financialService->calculate($branch, $customer, $subtotal, 'pickup')
            ),
            
            'delivery_logistics' => Inertia::defer(fn() => 
                $this->financialService->calculate($branch, $customer, $subtotal, 'delivery')
            ),

            'customer_location' => [
                'lat' => $customer->latitude,
                'lng' => $customer->longitude,
            ],
            
            'config' => [
                'reservation_minutes' => 10 
            ]
        ]);
    }
    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): RedirectResponse
    {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $customerId = (string) auth()->guard('customer')->id();

            $dto = CheckoutCartDTO::fromRequest($request, $customerId, $branchId);
            
            $order = $action->execute($dto);

            // RECTIFICACIÓN: Redirigir usando el objeto completo o el 'code'
            // Laravel usará automáticamente getRouteKeyName() que definimos como 'code'
            return redirect()->route('customer.order.show', $order) 
                ->with('success', 'Pedido reservado de forma segura.');

        } catch (Exception $e) {
            return back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }
}