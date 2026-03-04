<?php

namespace App\Actions\Customer\Order;

use App\Models\Cart;
use App\Models\Customer;
use Exception;

class GetCheckoutDataAction
{
    public function __construct(protected CalculateDeliveryFeeAction $deliveryFeeAction) {}

    public function execute(string $customerId, string $branchId): array
    {
        $customer = Customer::findOrFail($customerId);

        $cart = Cart::where('customer_id', $customerId)
            ->where('branch_id', $branchId)
            ->with(['items.sku', 'branch']) 
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception("Tu carrito está vacío.");
        }

        // Mapeo de items
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

        // 1. Tarifa para PickUp
        $baseServiceFee = $subtotal * ($cart->branch->base_service_fee_percentage / 100);
        $trustScore = max(0, min(100, (int) $customer->trust_score)); 
        $loyaltyMultiplier = 1 - ($trustScore / 100);
        $pickupServiceFee = round($baseServiceFee * $loyaltyMultiplier, 2);

        // 2. Direcciones de envío
        $addresses = $customer->addresses()
            ->where('branch_id', $branchId)
            ->orderByDesc('is_default')
            ->get();

        $addressesWithFees = $addresses->map(function($address) use ($cart, $customer, $subtotal) {
            $logistics = $this->deliveryFeeAction->execute($cart->branch, $address, $customer, (float) $subtotal);
            
            return [
                'id' => $address->id,
                'alias' => $address->alias,
                'address' => $address->address,
                'details' => $address->reference,
                'is_default' => $address->is_default,
                'logistics' => $logistics
            ];
        });

        return [
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
        ];
    }
}