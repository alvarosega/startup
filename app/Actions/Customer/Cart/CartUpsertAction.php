<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartUpsertAction
{
    public function __construct(protected CartService $cartService) {}

    public function execute(Request $request): void
    {
        $targetId = $request->input('target_id');
        $type     = $request->input('target_type'); 
        $quantity = (int) $request->input('quantity', 1);
        
        $mode = $request->input('mode', 'add');
        $isAbsolute = ($mode === 'set');
    
        $guestUuid = $request->header('X-Guest-UUID') ?? session()->get('guest_client_uuid');
    
        if ($type === 'sku') {
            $this->cartService->addSku($targetId, $quantity, $guestUuid, $isAbsolute);
        } else {
            // Pasamos custom_quantities desde el request (vital para el TemplateShow)
            $customQuantities = $request->input('custom_quantities', []);
            
            // Sincronización exacta con la firma del Service:
            // (id, qty, arrays_custom, uuid)
            $this->cartService->addBundle($targetId, $quantity, $customQuantities, $guestUuid);
        }
    }
}