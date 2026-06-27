<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Purchase;

use Illuminate\Foundation\Http\FormRequest;

readonly class CompletePurchaseDataDTO
{
    /**
     * @param array<string, array{lot_code: string, expiration_date: ?string}> $items
     */
    public function __construct(
        public array $items
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();
        $mapped = [];

        foreach ($validated['items'] as $item) {
            $mapped[$item['sku_id']] = [
                'lot_code' => (string) $item['lot_code'],
                'expiration_date' => $item['expiration_date'] ?? null
            ];
        }

        return new self($mapped);
    }
}