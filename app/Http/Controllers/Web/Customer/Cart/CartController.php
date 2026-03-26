<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Cart\CartUpsertAction;
use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Actions\Customer\Cart\UpdateCartItemAction;
use App\Actions\Customer\Cart\RemoveCartItemAction;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected ShopContextService $shopContext
    ) {}

    /**
     * Renderiza el agrupador de ítems (Soft-Cart).
     */
    public function index(Request $request, GetCustomerCartAction $action): Response
    {
        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $cart = $action->execute($guestUuid);

        return Inertia::render('Customer/Cart/Index', [
            'cart' => $cart,
            'shopContext' => [
                'branch_id' => $this->shopContext->getActiveBranchId()
            ]
        ]);
    }

    /**
     * Protocolo de Persistencia Atómica (+ o botón añadir).
     */
    public function upsert(Request $request, CartUpsertAction $action): RedirectResponse
    {
        // Validación técnica de entrada
        $request->validate([
            'target_id'   => ['required', 'string'],
            'target_type' => ['required', 'in:sku,bundle'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            $action->execute($request);
            
            return back()->with('toast', [
                'type'    => 'success',
                'message' => 'Ítem sincronizado con el carrito.'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Actualización manual de cantidades (Selector del carrito).
     */
    public function update(string $id, Request $request, UpdateCartItemAction $action): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99']
        ]);

        try {
            $branchId = $this->shopContext->getActiveBranchId();
            $action->execute($id, (int) $request->quantity, $branchId);

            return back()->with('toast', [
                'type'    => 'success',
                'message' => 'Cantidad actualizada.'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Eliminación de línea del agrupador.
     */
    public function remove(string $id, RemoveCartItemAction $action): RedirectResponse
    {
        try {
            $action->execute($id);

            return back()->with('toast', [
                'type'    => 'info',
                'message' => 'Ítem removido del carrito.'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }
}