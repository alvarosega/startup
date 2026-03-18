<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;
use App\DTOs\Customer\Cart\{AddBundleDTO, AddToCartDTO};
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;

class AddBundleToCartAction
{
    public function __construct(
        protected AddItemToCartAction $addItemAction,
        protected PriceResolverService $priceResolver
    ) {}

    public function execute(AddBundleDTO $dto): void
    {
        // 1. Carga crítica: Traemos el bundle con sus reglas originales
        $bundle = Bundle::with('skus')->findOrFail($dto->bundleId);

        DB::transaction(function () use ($bundle, $dto) {
            
            // Generamos un ID de grupo único para este pack en el carrito
            // Esto permite que el CartResource sepa que estos items van juntos
            $bundleGroupId = bin2hex(random_bytes(8));

            foreach ($bundle->skus as $sku) {
                // Si el pack NO es editable, ignoramos lo que mande el frontend 
                // y usamos la cantidad definida en la tabla pivote bundle_items
                $quantityPerPack = $bundle->is_editable 
                    ? ($dto->customItems[$sku->id] ?? $sku->pivot->quantity)
                    : $sku->pivot->quantity;

                $totalQuantity = $quantityPerPack * $dto->quantity;

                // 2. LA LEY DEL PRECIO:
                // Si el bundle tiene fixed_price, el item entra con precio prorrateado o cero
                // Si es editable (items agrupados), usamos el PriceResolverService
                $priceOverride = null;
                if ($bundle->fixed_price > 0) {
                    // Aquí podrías decidir si el precio se pone en el primer item 
                    // o se guarda una referencia al bundle_id en cart_items.
                }

                $this->addItemAction->execute(new AddToCartDTO(
                    skuId: $sku->id,
                    quantity: $totalQuantity,
                    branchId: $dto->branchId,
                    customerId: $dto->customerId,
                    guestUuid: $dto->guestUuid,
                    metadata: [
                        'bundle_id' => $bundle->id,
                        'bundle_group' => $bundleGroupId,
                        'is_pack' => !$bundle->is_editable
                    ]
                ));
            }
        });
    }
}