<?php

namespace App\DTOs\Price;

use Carbon\Carbon;

class PriceData
{
    public function __construct(
        public readonly string $skuId,
        public readonly ?int $branchId,
        public readonly float $finalPrice,
        public readonly float $listPrice, // Precio "tachado"
        public readonly string $type,     // 'regular', 'offer', 'wholesale'
        public readonly int $minQuantity,
        public readonly ?Carbon $validFrom,
        public readonly ?Carbon $validTo,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            skuId: $request->validated('sku_id'),
            branchId: $request->validated('branch_id'),
            finalPrice: (float) $request->validated('final_price'),
            // Si no envían precio de lista, usamos el final + 10% por defecto
            listPrice: (float) ($request->validated('list_price') ?? $request->validated('final_price')),
            type: $request->validated('type', 'regular'),
            minQuantity: (int) $request->validated('min_quantity', 1),
            validFrom: $request->date('valid_from') ?? now(),
            validTo: $request->date('valid_to'),
        );
    }

    public function toArray(): array
    {
        return [
            'sku_id' => $this->skuId,
            'branch_id' => $this->branchId,
            'final_price' => $this->finalPrice,
            'list_price' => $this->listPrice,
            'type' => $this->type,
            'min_quantity' => $this->minQuantity,
            'valid_from' => $this->validFrom,
            'valid_to' => $this->validTo,
        ];
    }
}