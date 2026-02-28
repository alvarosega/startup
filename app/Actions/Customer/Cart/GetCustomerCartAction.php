<?php

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;

class GetCustomerCartAction
{
    public function __construct(protected ShopContextService $contextService) {}

    // --- MODIFICAR LA CONSULTA Y ELIMINAR EL UPDATE ---
    public function execute(?string $guestUuid = null): ?Cart
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = Auth::guard('customer')->id();

        return Cart::query()
            ->where('branch_id', $branchId) // Filtro innegociable
            ->where(function ($query) use ($customerId, $guestUuid) {
                $customerId ? $query->where('customer_id', $customerId) 
                            : $query->where('session_id', $guestUuid);
            })
            ->with([
                'items.sku.product', 
                'items.sku.prices' => fn($q) => $q->where('branch_id', $branchId),
                'items.sku.inventoryLots' => fn($q) => $q->where('branch_id', $branchId)
                                                         ->where('is_safety_stock', false) // <--- CORTE QUIRÚRGICO AQUÍ
            ])
            ->first();
    }
    }