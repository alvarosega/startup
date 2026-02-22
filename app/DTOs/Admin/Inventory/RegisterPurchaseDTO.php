<?php

namespace App\DTOs\Admin\Inventory;

use App\Http\Requests\Admin\Inventory\RegisterPurchaseRequest;

readonly class RegisterPurchaseDTO
{
    public function __construct(
        public string $branch_id,
        public string $provider_id,
        public string $admin_id,
        public string $document_number,
        public string $purchase_date,
        public string $payment_type,
        public float $total_amount,
        public array $items,
        public ?string $payment_due_date = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(RegisterPurchaseRequest $request): self
    {
        return new self(
            branch_id:       $request->branch_id,
            provider_id:     $request->provider_id,
            admin_id:        auth('super_admin')->id(),
            document_number: $request->document_number,
            purchase_date:   $request->purchase_date,
            payment_type:    $request->payment_type,
            total_amount:    (float) $request->total_amount,
            payment_due_date: $request->payment_due_date,
            notes:           $request->notes,
            items:           array_map(fn($i) => new PurchaseItemDTO(...$i), $request->items)
        );
    }
}