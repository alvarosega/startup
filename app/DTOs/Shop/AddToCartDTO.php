<?php

namespace App\DTOs\Shop;

use Illuminate\Http\Request;
use App\Models\User;

class AddToCartDTO
{
    public function __construct(
        public readonly string $skuId, // <--- CAMBIO CRÍTICO: string (UUID)
        public readonly int $quantity,
        public readonly int $branchId, // Las sucursales suelen ser INT, si son UUID cámbialo aquí también
        public readonly ?User $user,
        public readonly ?string $sessionId,
    ) {}

    public static function fromRequest(Request $request, int $activeBranchId): self
    {
        return new self(
            skuId: $request->validated('sku_id'), // <--- CAMBIO: Quitamos el (int)
            quantity: (int) $request->validated('quantity'),
            branchId: $activeBranchId,
            user: $request->user(),
            sessionId: $request->session()->getId(),
        );
    }
}