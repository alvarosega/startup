<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;
use App\DTOs\Customer\Cart\{AddBundleDTO, AddToCartDTO};
use App\Actions\Customer\Cart\AddItemToCartAction;
use Illuminate\Support\Facades\DB;
use Exception;

class AddBundleToCartAction
{
    public function __construct(
        protected AddItemToCartAction $addItemAction
    ) {}

    public function execute(AddBundleDTO $dto): void
    {
        $bundle = Bundle::with('skus')->findOrFail($dto->bundleId);

        DB::transaction(function () use ($bundle, $dto) {
            foreach ($bundle->skus as $sku) {
                // Calculamos la cantidad real: qty_del_item_en_bundle * qty_de_bundles
                $totalSkuQuantity = $sku->pivot->quantity * $dto->quantity;

                $this->addItemAction->execute(new AddToCartDTO(
                    skuId: $sku->id,
                    quantity: $totalSkuQuantity,
                    branchId: $dto->branchId,
                    customerId: $dto->customerId,
                    guestUuid: $dto->guestUuid
                ));
            }
        });
    }
}