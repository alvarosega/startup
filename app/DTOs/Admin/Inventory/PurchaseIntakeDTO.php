<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

class PurchaseIntakeDTO
{
    public function __construct(
        public readonly string $branchId,
        public readonly string $providerId,
        public readonly string $adminId,
        public readonly string $documentNumber,
        public readonly string $purchaseDate,
        public readonly string $paymentType,
        public readonly array $items
    ) {}

    public static function fromRequest(array $data, string $adminId): self
    {
        return new self(
            branchId: $data['branch_id'],
            providerId: $data['provider_id'],
            adminId: $adminId,
            documentNumber: $data['document_number'],
            purchaseDate: $data['purchase_date'],
            paymentType: $data['payment_type'],
            items: array_map(fn($item) => [
                'sku_id' => $item['sku_id'],
                'quantity' => (float) $item['quantity'],
                'lot_code' => $item['lot_code'],
                'expiration_date' => $item['expiration_date'] ?? null
            ], $data['items'])
        );
    }
}