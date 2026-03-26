<?php

namespace App\DTOs\Admin\Inventory\Purchase;

readonly class RegisterPurchaseDTO {
    public function __construct(
        public string $idempotency_key,
        public string $branch_id,
        public string $provider_id,
        public string $admin_id,
        public string $purchase_date,
        public string $payment_type,
        public bool $is_emergency,
        public string $total_amount, // Mantenemos como string para precisión decimal
        public array $items,
    ) {}

    public static function fromRequest($request): self {
        return new self(
            idempotency_key: $request->header('X-Idempotency-Key') ?? throw new \Exception("PROTOCOL_VIOLATION: Missing Idempotency Key"),
            branch_id:     $request->branch_id,
            provider_id:   $request->provider_id,
            admin_id:      auth('super_admin')->id(),
            purchase_date: $request->purchase_date,
            payment_type:  $request->payment_type,
            is_emergency:  (bool) $request->is_emergency,
            total_amount:  (string) $request->total_amount,
            items:         $request->items
        );
    }
}