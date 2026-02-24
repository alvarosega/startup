<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

// Arquitectura Core
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\SyncGuestCartAction;
use App\Actions\Customer\Cart\AddItemToCartAction;
use App\DTOs\Customer\Cart\SyncCartDTO;
use App\DTOs\Customer\Cart\AddToCartDTO;
use App\Http\Requests\Customer\Cart\AddToCartRequest;
use App\Models\Cart;
use App\Http\Resources\Customer\Cart\CartItemResource;
use App\Http\Resources\Customer\Cart\CartResource;


class CartController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function index(Request $request): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = auth('customer')->id();
        $guestUuid = $request->query('guest_id') ?? $request->header('X-Guest-UUID');
    
        $cart = Cart::query()
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($customerId, $guestUuid) {
                $customerId ? $query->where('customer_id', $customerId) 
                            : $query->where('session_id', $guestUuid);
            })
            ->with([
                'items.sku.product', 
                'items.sku.prices' => fn($q) => $q->where('branch_id', $branchId),
                'items.sku.inventoryLots' => fn($q) => $q->where('branch_id', $branchId)
            ])
            ->first();
    
        return Inertia::render('Customer/Cart/Index', [
            // resolve() elimina la llave 'data' exterior
            'cart' => $cart ? (new CartResource($cart))->resolve() : null
        ]);
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