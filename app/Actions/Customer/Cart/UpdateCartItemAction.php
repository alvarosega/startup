<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function __construct(
        protected CartService $cartService // Inyectamos el motor central
    ) {}

    /**
     * Delega la actualización al CartService para garantizar stock y precio dinámico.
     */
    public function execute(string $cartItemId, int $newQuantity): object
    {
        // 1. Localizamos el ítem para obtener el SKU_ID
        $item = CartItem::select('sku_id', 'is_bundle')->findOrFail($cartItemId);

        if ($item->is_bundle) {
            // Aquí llamaríamos a la lógica de bundles cuando la definas
            return (object) ['success' => false, 'message' => 'Actualización de combos pendiente.'];
        }

        // 2. DELEGACIÓN ATÓMICA: Usamos addSku con isAbsolute = true
        // Esto ejecutará: Validación de Stock -> Precio Dinámico -> Update en DB
        // El guestUuid se recuperará automáticamente dentro del Service si es null
        return $this->cartService->addSku(
            skuId:      (string) $item->sku_id,
            quantity:   $newQuantity,
            guestUuid:  null, 
            isAbsolute: true // <--- CRÍTICO: Indica que es "fijar cantidad" y no "sumar"
        );
    }
}