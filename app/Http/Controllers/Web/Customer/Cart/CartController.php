<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response};
use Illuminate\Support\Facades\Auth;
use App\Services\ShopContextService;
use App\DTOs\Customer\Cart\{SyncCartDTO, AddToCartDTO};
use App\Http\Requests\Customer\Cart\{AddToCartRequest, UpdateCartRequest};
use App\Http\Resources\Customer\Cart\CartResource;
use App\Actions\Customer\Cart\{
    GetCustomerCartAction,
    UpdateCartItemAction,
    RemoveCartItemAction,
    SyncGuestCartAction,
    AddItemToCartAction
};

class CartController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    /**
     * Renderiza el carrito con resolución de precios dinámica.
     */
    public function index(Request $request, GetCustomerCartAction $getCartAction): Response
    {
        // Prioridad: Header X-Guest-UUID > Query guest_id
        $guestUuid = $request->header('X-Guest-UUID'); 
    
        $cart = $getCartAction->execute($guestUuid);
    
        return Inertia::render('Customer/Cart/Index', [
            // El Resource se encarga de llamar al PriceResolverService internamente
            'cart' => $cart ? (new CartResource($cart))->resolve() : null
        ]);
    }

    /**
     * Actualiza cantidades y dispara la recalificación de precios.
     */
    public function update(UpdateCartRequest $request, string $id, UpdateCartItemAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();

        try {
            // La acción debe verificar stock y aplicar el nuevo escalón de precio
            $action->execute($id, $request->validated('quantity'), $branchId);
            
            return back()->with('success', 'Carrito actualizado.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function remove(string $id, RemoveCartItemAction $action): RedirectResponse
    {
        try {
            $action->execute($id);
            return back()->with('success', 'Producto removido.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => 'No se pudo eliminar el ítem.']);
        }
    }

    /**
     * Fusión atómica de carritos tras el Login.
     */
    public function sync(Request $request, SyncGuestCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $guestUuid = $request->input('guest_client_uuid');
        $customerId = Auth::guard('customer')->id();

        if (!$guestUuid || !$customerId) {
            return redirect()->route('customer.shop.index');
        }

        $dto = SyncCartDTO::fromRequest($customerId, $guestUuid, $branchId);
        
        try {
            $action->execute($dto);
            return redirect()->route('customer.cart.index')
                ->with('success', 'Carrito sincronizado.');
        } catch (\Exception $e) {
            return redirect()->route('customer.cart.index')
                ->withErrors(['sync' => 'Error en sincronización: ' . $e->getMessage()]);
        }
    }

    public function store(AddToCartRequest $request, AddItemToCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $dto = AddToCartDTO::fromRequest($request, $branchId);

        try {
            $action->execute($dto);
            return back()->with('success', 'Añadido al pedido.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }
}