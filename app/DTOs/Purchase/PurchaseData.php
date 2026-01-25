<?php
namespace App\DTOs\Purchase;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use Carbon\Carbon;

class PurchaseData
{
    public function __construct(
        public readonly int $branch_id,
        public readonly string $provider_id,
        public readonly string $document_number,
        public readonly Carbon $purchase_date,
        public readonly string $payment_type,
        public readonly ?Carbon $payment_due_date,
        public readonly ?string $notes,
        public readonly float $total_amount, // Calculado o validado
        /** @var PurchaseItemDTO[] */
        public readonly array $items
    ) {}

    public static function fromRequest(StorePurchaseRequest $request): self
    {
        $items = array_map(fn($item) => new PurchaseItemDTO(
            sku_id: $item['sku_id'],
            quantity: (int) $item['quantity_input'],
            unit_cost: (float) ($item['total_cost_input'] / $item['quantity_input']), // Recálculo por seguridad
            total_cost: (float) $item['total_cost_input'],
            expiration_date: $item['expiration_date'] ?? null,
        ), $request->validated('items'));

        // Recalcular total del backend para evitar manipulaciones de frontend
        $grandTotal = array_reduce($items, fn($sum, $item) => $sum + $item->total_cost, 0);

        return new self(
            branch_id: (int) $request->validated('branch_id'),
            provider_id: $request->validated('provider_id'),
            document_number: $request->validated('document_number'),
            purchase_date: Carbon::parse($request->validated('purchase_date')),
            payment_type: $request->validated('payment_type'),
            payment_due_date: $request->validated('payment_due_date') ? Carbon::parse($request->validated('payment_due_date')) : null,
            notes: $request->validated('notes'),
            total_amount: $grandTotal,
            items: $items
        );
    }
}