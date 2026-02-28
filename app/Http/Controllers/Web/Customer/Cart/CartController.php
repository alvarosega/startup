<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\ShopContextService;
use App\DTOs\Customer\Cart\SyncCartDTO;
use App\DTOs\Customer\Cart\AddToCartDTO;
use App\Http\Requests\Customer\Cart\AddToCartRequest;
use App\Models\Cart;
use App\Http\Resources\Customer\Cart\CartItemResource;
use App\Http\Resources\Customer\Cart\CartResource;
use App\Models\CartItem; 
use App\Models\InventoryLot;
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

    public function index(Request $request, GetCustomerCartAction $getCartAction): Response
    {
        $guestUuid = $request->query('guest_id') ?? $request->header('X-Guest-UUID');
        
        $cart = $getCartAction->execute($guestUuid);
    
        return Inertia::render('Customer/Cart/Index', [
            'cart' => $cart ? (new CartResource($cart))->resolve() : null
        ]);
    }

    public function update(Request $request, string $id, UpdateCartItemAction $action): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $branchId = $this->contextService->getActiveBranchId();

        try {
            $action->execute($id, $request->quantity, $branchId);
            return back()->with('success', 'Cantidad actualizada.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function remove(string $id, RemoveCartItemAction $action): RedirectResponse
    {
        try {
            $action->execute($id);
            return back()->with('success', 'Producto eliminado.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => 'No se pudo eliminar el producto.']);
        }
    }
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
                ->with('success', 'Carrito fusionado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }

    /**
     * Adición de producto. El DTO maneja el tipado estricto de UUID Strings.
     */
    public function store(AddToCartRequest $request, AddItemToCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        // El DTO debe aceptar string para $branchId (UUID)
        $dto = AddToCartDTO::fromRequest($request, $branchId);

        try {
            $action->execute($dto);
            return back()->with('success', 'Producto añadido al carrito.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }
}