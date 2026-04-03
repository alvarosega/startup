<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class UpsertCartItemDTO
{
    public int $quantity;
    public string $targetId;

    public function __construct(
        string $targetId,
        public string $targetType,
        int $quantity,
        public string $branchId,
        public array $customItems = []
    ) {
        // LEY DE NORMALIZACIÓN: Clamping estricto [1-99]
        $this->quantity = max(1, min(99, $quantity));
        
        // VALIDACIÓN DE IDENTIDAD: Asegurar formato UUID antes de tocar DB
        if (!Str::isUuid($targetId)) {
            throw new \InvalidArgumentException("PROTOCOLO_IDENTIDAD_VIOLADO: ID malformado.");
        }
        $this->targetId = $targetId;
    }

    public static function fromRequest(Request $request, string $branchId): self
    {
        return new self(
            targetId: (string) $request->input('target_id'),
            targetType: (string) $request->input('target_type', 'sku'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $branchId,
            customItems: (array) $request->input('items', [])
        );
    }
}