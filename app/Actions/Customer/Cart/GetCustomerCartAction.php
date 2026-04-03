<?php
//ok
namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Customer\Cart\CartResource;

class GetCustomerCartAction
{
    public function __construct(protected ShopContextService $contextService) {}

    public function execute(?string $guestUuid = null): array
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = Auth::guard('customer')->id();
        $now = now(); // Tiempo Atómico para toda la petición

        if (!$customerId && !$guestUuid) {
            return $this->emptyCartResponse();
        }

        $cart = Cart::query()
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($customerId, $guestUuid) {
                $customerId ? $query->where('customer_id', $customerId) 
                            : $query->where('session_id', $guestUuid);
            })
            ->with([
                'items.bundle.skus.product.brand',
                'items.sku.product.brand',
                // INTEGRIDAD: Filtramos precios por sucursal ANTES de que lleguen al Resolver
                'items.sku.prices' => function($q) use ($branchId, $now) {
                    $q->where('branch_id', $branchId)
                      ->where('valid_from', '<=', $now)
                      ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now));
                }
            ])
            ->first();

        if (!$cart) return $this->emptyCartResponse();

        // Pasamos el tiempo atómico a través de la propiedad 'additional' del Resource
        return (new CartResource($cart))
            ->additional(['atomic_now' => $now])
            ->resolve();
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