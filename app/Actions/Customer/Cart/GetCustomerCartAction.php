<?php

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Customer\Cart\CartResource;

class GetCustomerCartAction
{
    public function __construct(protected ShopContextService $contextService) {}

    public function execute(?string $guestUuid = null): array // Cambiamos ?array a array
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = Auth::guard('customer')->id();

        // Si no hay rastro de identidad, devolvemos objeto vacío
        if (!$customerId && !$guestUuid) {
            return $this->emptyCartResponse();
        }

        $cart = Cart::query()
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($customerId, $guestUuid) {
                $customerId ? $query->where('customer_id', $customerId) 
                            : $query->where('session_id', $guestUuid);
            })
            ->with(['items.sku.inventoryLots', 'items.sku.prices'])
            ->first();

        return $cart ? (new \App\Http\Resources\Customer\Cart\CartResource($cart))->resolve() 
                     : $this->emptyCartResponse();
    }

    private function emptyCartResponse(): array 
    {
        return [
            'id' => null,
            'items' => [],
            'total_items' => 0,
            'total_price' => 0,
            'total_savings' => 0
        ];
    }
}