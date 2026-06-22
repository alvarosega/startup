<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Adjustment;

use Illuminate\Http\Request;

readonly class TransferToSafetyData
{
    public function __construct(
        public string $inventory_lot_id,
        public float $quantity,
        public string $reason
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            inventory_lot_id: (string) $validated['inventory_lot_id'],
            quantity: (float) $validated['quantity'],
            reason: trim((string) $validated['reason'])
        );
    }
}