<?php

namespace App\DTOs\Admin\Inventory;

readonly class RegisterPurchaseDTO
{
    public function __construct(
        public string $branch_id,
        public string $provider_id,
        public string $admin_id,
        public string $purchase_date,
        public string $payment_type,
        public bool $is_emergency,
        public float $total_amount,
        public array $items,
        public ?string $notes = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            branch_id:     $request->branch_id,
            provider_id:   $request->provider_id,
            admin_id:      auth('super_admin')->id(), // <--- GUARD EXPLÍCITO
            purchase_date: $request->purchase_date,
            payment_type:  $request->payment_type,
            is_emergency:  $request->is_emergency,
            total_amount:  (float) $request->total_amount,
            notes:         $request->notes,
            items:         $request->items
        );
    }
}