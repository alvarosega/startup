<?php

namespace App\DTOs\Purchase;

use App\DTOs\Purchase\PurchaseItemData;

class PurchaseData
{
    public function __construct(
        public readonly int $branchId,
        public readonly string $providerId,
        public readonly string $userId,
        public readonly string $documentNumber,
        public readonly string $purchaseDate,
        public readonly string $paymentType,
        public readonly ?string $paymentDueDate,
        public readonly ?string $notes,
        /** @var PurchaseItemData[] */
        public readonly array $items
    ) {}

    public static function fromRequest($request): self
    {
        $user = $request->user();
        
        // Lógica de seguridad para Branch Admin
        $targetBranchId = $user->hasRole('branch_admin') 
            ? $user->branch_id 
            : $request->validated('branch_id');

        $items = array_map(
            fn($item) => PurchaseItemData::fromArray($item), 
            $request->validated('items')
        );

        return new self(
            branchId: (int) $targetBranchId,
            providerId: $request->validated('provider_id'),
            userId: $user->id,
            documentNumber: $request->validated('document_number'),
            purchaseDate: $request->validated('purchase_date'),
            paymentType: $request->validated('payment_type'),
            paymentDueDate: $request->validated('payment_due_date'),
            notes: $request->validated('notes'),
            items: $items
        );
    }
}