<?php

namespace App\DTOs\Shop;

use App\Models\User;
use Illuminate\Http\Request;

class CreateOrderDTO
{
    public function __construct(
        public readonly User $user,
        public readonly int $branchId,
        public readonly string $deliveryType, 
        
        // CORRECCIÓN: Acepta string (UUID) o null
        public readonly ?string $addressId, 
        
        public readonly ?string $sessionId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $addressId = $request->validated('address_id');

        return new self(
            user: $request->user(),
            branchId: $request->user()->branch_id, 
            deliveryType: $request->validated('delivery_type'),
            
            // CORRECCIÓN: Convertimos a string si existe, si no, null
            addressId: $addressId ? (string) $addressId : null,
            
            sessionId: $request->session()->getId(),
        );
    }
}