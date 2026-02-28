<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;
use App\DTOs\Customer\Cart\{AddBundleDTO, AddToCartDTO};
use Illuminate\Support\Facades\DB;

class AddBundleToCartAction
{
    public function __construct(protected AddItemToCartAction $addItemAction) {}

    public function execute(AddBundleDTO $dto): void
    {
        $bundle = Bundle::with('skus')->findOrFail($dto->bundleId);

        DB::transaction(function () use ($bundle, $dto) {
            foreach ($bundle->skus as $sku) {
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