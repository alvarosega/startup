<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Removal;

use Illuminate\Foundation\Http\FormRequest;

readonly class RemovalDataDTO
{
    /**
     * @param array<RemovalItemDataDTO> $items
     */
    public function __construct(
        public string $branchId,
        public string $reason,
        public ?string $notes,
        public array $items
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();
        $mappedItems = [];

        foreach ($validated['items'] as $item) {
            $mappedItems[] = new RemovalItemDataDTO(
                inventoryLotId: (string) $item['inventory_lot_id'],
                quantity: (float) $item['quantity'],
                unitCost: (float) $item['unit_cost']
            );
        }

        return new self(
            branchId: (string) $validated['branch_id'],
            reason: (string) $validated['reason'],
            notes: $validated['notes'] ?? null,
            items: $mappedItems
        );
    }
}