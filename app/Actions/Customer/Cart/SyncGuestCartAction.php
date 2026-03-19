<?php

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\DTOs\Customer\Cart\SyncCartDTO;
use Illuminate\Support\Facades\DB;

class SyncGuestCartAction
{
    public function __construct(
        protected SyncRegularProductAction $syncRegular,
        protected SyncBundleProductAction $syncBundle
    ) {}

    public function execute(SyncCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // 1. Buscamos TODOS los carritos que el invitado creó en cualquier sucursal
            $guestCarts = Cart::where('session_id', $dto->guestUuid)
                ->whereNull('customer_id')
                ->with('items')
                ->get();
        
            if ($guestCarts->isEmpty()) return;

            foreach ($guestCarts as $guestCart) {
                // 2. Mapeo 1:1 -> El carrito de la Sucursal A del invitado 
                // pasa al carrito de la Sucursal A del Cliente.
                $customerCart = Cart::firstOrCreate([
                    'customer_id' => $dto->customerId, 
                    'branch_id'   => $guestCart->branch_id 
                ]);

                foreach ($guestCart->items as $item) {
                    if ($item->is_bundle) {
                        $this->syncBundle->execute($item, $customerCart);
                    } else {
                        $this->syncRegular->execute($item, $customerCart);
                    }
                }

                // 3. Limpieza: El rastro anónimo desaparece
                $guestCart->forceDelete();
            }
        });
    }
}