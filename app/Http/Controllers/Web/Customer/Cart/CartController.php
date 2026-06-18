<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Cart\UpsertCartItemRequest;
use App\DTOs\Customer\Cart\UpsertCartItemData;
use App\Http\Resources\Customer\Cart\CartResource;
use App\Actions\Customer\Cart\{
    GetCustomerCartAction,
    CartUpsertAction,
    UpdateCartItemAction,
    RemoveCartItemAction
};
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected readonly ShopContextService $shopContext
    ) {}

    public function index(Request $request, GetCustomerCartAction $action): Response
    {
        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $branchId  = $this->shopContext->getActiveBranchId();
        $userId    = auth()->guard('customer')->id();

        return Inertia::render('Customer/Cart/Index', [
            // RECTIFICACIÓN: Se invoca ->resolve() dentro del callback diferido para aplanar la respuesta y destruir el wrapper 'data'
            'cart' => Inertia::defer(fn() => 
                (new CartResource($action->execute($guestUuid, $userId, $branchId)))->resolve()
            ),
            'shopContext' => ['branch_id' => $branchId]
        ]);
    }

    public function upsert(UpsertCartItemRequest $request, CartUpsertAction $action): RedirectResponse
    {
        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        
        $action->execute(UpsertCartItemData::fromRequest($request), $guestUuid);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Selección añadida al carrito.'
        ]);
    }

    public function update(string $id, Request $request, UpdateCartItemAction $action): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99']
        ]);

        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        
        $action->execute($id, (int) $request->quantity, $guestUuid);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Cantidad de volumen actualizada.'
        ]);
    }

    public function remove(string $id, RemoveCartItemAction $action): RedirectResponse
    {
        $action->execute($id);

        return back()->with('toast', [
            'type'    => 'info',
            'message' => 'Artículo removido del carrito.'
        ]);
    }
}