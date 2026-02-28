<?php

namespace App\Http\Controllers\Web\Customer\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Order\CheckoutRequest;
use App\Actions\Customer\Order\CheckoutCartAction;
use App\Actions\Customer\Order\CalculateDeliveryFeeAction;
use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Services\ShopContextService;
use App\Models\Cart;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

class CheckoutController extends Controller
{
    public function __construct(protected ShopContextService $contextService) {}

    public function index(CalculateDeliveryFeeAction $deliveryFeeAction): Response|RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customer = auth()->guard('customer')->user();
    
        // Cargamos el carrito con la relación a la sucursal para obtener su nombre y métricas
        $cart = Cart::where('customer_id', $customer->id)
            ->where('branch_id', $branchId)
            ->with(['items.sku', 'branch']) 
            ->first();
    
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('customer.shop.index')
                ->with('error', 'Tu carrito está vacío.');
        }
    
        // Mapeo de items para el frontend
        $mappedItems = $cart->items->map(function ($item) {
            $price = $item->sku->base_price; 
            return [
                'name' => $item->sku->name,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'subtotal' => $price * $item->quantity,
            ];
        });
    
        $subtotal = $mappedItems->sum('subtotal');

        // --- CÁLCULOS LOGÍSTICOS PREVIOS ---
        
        // 1. Tarifa para modalidad PickUp (Recojo en tienda). Delivery es 0.
        $baseServiceFee = $subtotal * ($cart->branch->base_service_fee_percentage / 100);
        $trustScore = max(0, min(100, (int) $customer->trust_score)); 
        $loyaltyMultiplier = 1 - ($trustScore / 100);
        $pickupServiceFee = round($baseServiceFee * $loyaltyMultiplier, 2);
        // 2. Direcciones de envío con sus tarifas pre-calculadas
        $addresses = $customer->addresses()
            ->where('branch_id', $branchId)
            ->orderByDesc('is_default')
            ->get();

        $addressesWithFees = $addresses->map(function($address) use ($deliveryFeeAction, $cart, $customer, $subtotal) {
            $logistics = $deliveryFeeAction->execute($cart->branch, $address, $customer, (float) $subtotal);
            
            return [
                'id' => $address->id,
                'alias' => $address->alias,
                'address' => $address->address,
                'details' => $address->reference,
                'is_default' => $address->is_default,
                'logistics' => $logistics // Contiene delivery_fee, service_fee, total_logistics, etc.
            ];
        });
    
        return Inertia::render('Customer/Shop/Checkout/Index', [
            'cart' => [
                'id' => $cart->id,
                'branch_id' => $cart->branch_id,
                'branch_name' => $cart->branch->name,
                'items' => $mappedItems,
                'subtotal' => $subtotal, 
            ],
            'pickup_logistics' => [
                'delivery_fee' => 0.00,
                'original_service_fee' => round($baseServiceFee, 2),
                'service_fee' => $pickupServiceFee,
                'savings' => round($baseServiceFee - $pickupServiceFee, 2),
                'total_logistics' => $pickupServiceFee
            ],
            'addresses' => $addressesWithFees,
            'default_address_id' => $addressesWithFees->where('is_default', true)->first()['id'] ?? null,
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = auth()->guard('customer')->id();

        $dto = CheckoutCartDTO::fromRequest($request, $branchId, $customerId);

        try {
            $order = $action->execute($dto);
            
            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Orden reservada. Tienes 10 minutos para subir tu comprobante de pago.');

        } catch (Exception $e) {
            return back()->withErrors(['checkout_error' => $e->getMessage()]);
        }
    }
}