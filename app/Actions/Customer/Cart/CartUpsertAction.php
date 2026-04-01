<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartUpsertAction
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Ejecuta la lógica de persistencia y retorna el objeto de estado del Service.
     */
    public function execute(Request $request): object
    {
        $targetId = $request->input('target_id');
        $type     = $request->input('target_type'); 
        $quantity = (int) $request->input('quantity', 1);
        $mode     = $request->input('mode', 'add');
        $isAbsolute = ($mode === 'set');

        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');

        if ($type === 'sku') {
            // Retornamos el objeto {success, code, message, meta}
            return $this->cartService->addSku($targetId, $quantity, $guestUuid, $isAbsolute);
        }

        // Lógica de Bundles (Pendiente según tu instrucción II.2)
        return (object) ['success' => false, 'message' => 'Tipo de producto no soportado.'];
    }
}