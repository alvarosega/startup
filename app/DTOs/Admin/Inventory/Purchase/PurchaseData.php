<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Purchase;

use Illuminate\Http\Request;

readonly class PurchaseData
{
    /**
     * @param PurchaseItemData[] $items
     */
    public function __construct(
        public string $branch_id,
        public string $provider_id,
        public string $document_number,
        public string $purchase_date,
        public string $payment_type,
        public string $lot_code,
        public ?string $expiration_date,
        public array $items
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        $items = array_map(
            fn(array $item) => new PurchaseItemData(
                sku_id: (string) $item['sku_id'],
                quantity: (float) $item['quantity']
            ),
            $validated['items']
        );

        return new self(
            branch_id: (string) $validated['branch_id'],
            provider_id: (string) $validated['provider_id'],
            document_number: trim((string) $validated['document_number']),
            purchase_date: (string) $validated['purchase_date'],
            payment_type: (string) $validated['payment_type'],
            lot_code: trim((string) $validated['lot_code']), // Lote único global para todo el documento
            expiration_date: $validated['expiration_date'] ?? null,
            items: $items
        );
    }
}