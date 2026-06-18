<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Cart;

use App\Http\Requests\Customer\Cart\UpsertCartItemRequest;

/**
 * DTO Inmutable para la estandarización de payloads de entrada del carrito.
 */
final readonly class UpsertCartItemData
{
    public function __construct(
        public string $targetId,
        public string $targetType,
        public int $quantity
    ) {}

    public static function fromRequest(UpsertCartItemRequest $request): self
    {
        return new self(
            targetId: $request->validated('target_id'),
            targetType: $request->validated('target_type'),
            quantity: (int) $request->validated('quantity')
        );
    }
}