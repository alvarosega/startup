<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

readonly class PurchaseDataDTO
{
    /**
     * @param array<PurchaseItemDataDTO> $items
     */
    public function __construct(
        public string $branchId,
        public string $providerId,
        public string $documentNumber,
        public string $purchaseDate,
        public string $paymentType,
        public array $items
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();
        $mappedItems = [];

        foreach ($validated['items'] as $item) {
            $mappedItems[] = new PurchaseItemDataDTO(
                skuId: (string) $item['sku_id'],
                quantity: (float) $item['quantity'],
                costPrice: (float) $item['cost_price'],
                lotCode: isset($item['lot_code']) ? (string) $item['lot_code'] : null,
                expirationDate: isset($item['expiration_date']) ? (string) $item['expiration_date'] : null
            );
        }

        return new self(
            branchId: (string) $validated['branch_id'],
            providerId: (string) $validated['provider_id'],
            documentNumber: (string) $validated['document_number'],
            purchaseDate: (string) $validated['purchase_date'],
            paymentType: (string) $validated['payment_type'],
            items: $mappedItems
        );
    }
}