<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
// --- REEMPLAZAR EL MÉTODO toArray ---
    public function toArray(Request $request): array
    {
        $now = $this->additional['atomic_now'] ?? now();

        // 1. Resolvemos individualmente para asegurar un Array plano y pasar el contexto 'now'
        $items = $this->items->map(function($item) use ($now) {
            return (new CartItemResource($item))
                ->additional(['atomic_now' => $now])
                ->resolve();
        })->toArray();

        return [
            'id'            => (string) $this->id,
            'items'         => $items, // <--- Ahora es un Array []
            'total_items'   => (int) collect($items)->sum('quantity'),
            'total_price'   => (float) collect($items)->sum('subtotal'),
            'total_savings' => (float) collect($items)->sum('line_savings'),
        ];
    }
}