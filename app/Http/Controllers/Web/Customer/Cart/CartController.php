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
    /**
 * Procesa la adición de un bundle configurable al carrito.
 */
    public function addConfigurableBundle(Request $request)
    {
        $request->validate([
            'bundle_id' => 'required|exists:bundles,id',
            'items'     => 'required|array', // [sku_id => quantity]
        ]);

        $branchId = $this->contextService->getActiveBranchId();
        $bundle = Bundle::with('items')->findOrFail($request->bundle_id);

        // LOGICA DE SEGURIDAD:
        foreach ($request->items as $skuId => $quantity) {
            $bundleItem = $bundle->items->where('sku_id', $skuId)->first();
            
            // 1. ¿El SKU pertenece realmente a este bundle?
            if (!$bundleItem) abort(403, "SKU no pertenece al bundle.");

            // 2. ¿Excede el límite permitido por el bundle?
            if ($quantity > $bundleItem->quantity) {
                abort(422, "Excede la cantidad permitida para este ítem.");
            }
        }

        // Si todo es correcto, guardamos en la tabla 'cart_items'
        // El 'cart_id' se obtiene del middleware o sesión
        // ... lógica de persistencia en DB ...

        return back()->with('success', 'Combo añadido correctamente.');
    }
}