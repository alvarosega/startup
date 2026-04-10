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
use App\Actions\Customer\Bundle\AddTemplateToCartAction;

class CartController extends Controller
{
    public function __construct(
        protected ShopContextService $shopContext
    ) {}

    public function index(Request $request, GetCustomerCartAction $action): Response
    {
        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $branchId  = $this->shopContext->getActiveBranchId();
        $userId    = auth()->guard('customer')->id();
    
        // RECTIFICACIÓN: Añadir ->resolve() al final de la ejecución de la acción
        $cart = $action->execute($guestUuid, $userId, $branchId)->resolve();
    
        return Inertia::render('Customer/Cart/Index', [
            'cart' => $cart,
            'shopContext' => [
                'branch_id' => $branchId
            ]
        ]);
    }

    public function upsert(Request $request, CartUpsertAction $action): RedirectResponse
    {
        $request->validate([
            'target_id'   => ['required', 'string'],
            'target_type' => ['required', 'in:sku,bundle'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $result = $action->execute($request);

        if (!$result->success) {
            // INTEGRIDAD: Si falla el stock, devolvemos error de validación para que Inertia lo pinte
            return back()->withErrors(['cart' => $result->message]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'message' => $result->message
        ]);
    }

    public function update(string $id, Request $request, UpdateCartItemAction $action): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99']
        ]);

        // Ya no necesitamos pasar el branchId aquí, el Service lo resuelve solo
        $result = $action->execute($id, (int) $request->quantity);

        if (!$result->success) {
            return back()->withErrors(['cart' => $result->message]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'message' => $result->message
        ]);
    }
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
    public function addTemplate(Request $request, AddTemplateToCartAction $action): RedirectResponse
    {
        $request->validate([
            'bundle_id' => ['required', 'string', 'exists:bundles,id']
        ]);
    
        $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $result = $action->execute($request->bundle_id, $guestUuid);
    
        if (!$result->success) {
            return back()->withErrors(['cart' => $result->message]);
        }
    
        // RECTIFICACIÓN DE RUTA: Redirección nominal al Carrito de Compras
        return redirect()->route('customer.cart.index')->with('toast', [
            'type'    => 'success',
            'message' => '¡Pack añadido! Revisa tu selección.'
        ]);
    }
}