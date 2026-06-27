<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

readonly class IsolateQuarantineDataDTO
{
    public function __construct(
        public string $inventoryLotId,
        public bool $isQuarantine
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            inventoryLotId: (string) $validated['inventory_lot_id'],
            isQuarantine: (bool) $validated['is_quarantine']
        );
    }
}