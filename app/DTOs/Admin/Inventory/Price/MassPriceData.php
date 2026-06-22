<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Price;

use Illuminate\Http\Request;

readonly class MassPriceData
{
    /**
     * @param string[] $selected_skus
     */
    public function __construct(
        public array $selected_skus,
        public string $branch_id,
        public string $type,
        public float $list_price,
        public float $final_price,
        public int $min_quantity,
        public int $priority,
        public string $valid_from,
        public ?string $valid_to
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            selected_skus: (array) $validated['selected_skus'],
            branch_id: (string) $validated['branch_id'],
            type: (string) $validated['type'],
            list_price: (float) $validated['list_price'],
            final_price: (float) $validated['final_price'],
            min_quantity: (int) ($validated['min_quantity'] ?? 1),
            priority: (int) ($validated['priority'] ?? 1),
            valid_from: (string) $validated['valid_from'],
            valid_to: $validated['valid_to'] ?? null
        );
    }
}