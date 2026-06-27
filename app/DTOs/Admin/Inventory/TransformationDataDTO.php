<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

readonly class TransformationDataDTO
{
    public function __construct(
        public string $branchId,
        public string $sourceInventoryLotId,
        public float $quantityRemoved,
        public string $destinationSkuId,
        public float $quantityAdded,
        public ?string $destinationExpirationDate
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            branchId: (string) $validated['branch_id'],
            sourceInventoryLotId: (string) $validated['source_inventory_lot_id'],
            quantityRemoved: (float) $validated['quantity_removed'],
            destinationSkuId: (string) $validated['destination_sku_id'],
            quantityAdded: (float) $validated['quantity_added'],
            destinationExpirationDate: isset($validated['destination_expiration_date']) ? (string) $validated['destination_expiration_date'] : null
        );
    }
}