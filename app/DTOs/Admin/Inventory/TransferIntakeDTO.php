<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

class TransferIntakeDTO
{
    public function __construct(
        public readonly string $transferId,
        public readonly string $adminId,
        public readonly array $items
    ) {}

    public static function fromRequest(string $transferId, string $adminId, array $data): self
    {
        return new self(
            transferId: $transferId,
            adminId: $adminId,
            items: array_map(fn($item) => [
                'sku_id' => $item['sku_id'],
                'qty_received' => (float) $item['qty_received']
            ], $data['items'])
        );
    }
}