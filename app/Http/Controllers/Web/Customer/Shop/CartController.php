<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Arquitectura
use App\Services\ShopContextService;
use App\Http\Requests\Shop\AddToCartRequest; // Validación
use App\DTOs\Shop\AddToCartDTO;              // Transferencia
use App\Actions\Shop\AddItemToCartAction;    // Lógica
use App\Models\Cart;                         
use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Shop\BulkAddToCartRequest;
use App\DTOs\Shop\BulkAddToCartDTO;
use App\Actions\Shop\AddBulkItemsToCartAction;




class CartController extends Controller
{
    protected $contextService;

    public function __construct(ShopContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function index(Request $request)
    {
        $activeBranchId = $this->contextService->getActiveBranchId();
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // 1. Obtener Carrito (Sin filtrar por branch_id aún para detectar si existe uno viejo)
        $cart = Cart::where(fn($q) => $user ? $q->where('user_id', $user->id) : $q->where('session_id', $sessionId))
                    ->latest()
                    ->with(['items.sku.product', 'items.sku.prices'])
                    ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return Inertia::render('Shop/Cart/Index', ['cart' => null, 'items' => []]);
        }

        // --- LÓGICA DE AUTO-MIGRACIÓN Y SANACIÓN ---
        // Si el carrito pertenece a una sucursal distinta a la activa, lo migramos.
        if ($cart->branch_id !== $activeBranchId) {
            
            // A. Actualizamos la sucursal del carrito
            $cart->update(['branch_id' => $activeBranchId]);

            // B. Validamos y Ajustamos Stock en la NUEVA sucursal
            foreach ($cart->items as $item) {
                // Stock Real = Físico - Reservado
                $stockAvailable = InventoryLot::where('branch_id', $activeBranchId)
                    ->where('sku_id', $item->sku_id)
                    ->sum(DB::raw('quantity - reserved_quantity'));

                if ($stockAvailable <= 0) {
                    $item->delete(); // Eliminar si no existe en la nueva zona
                } elseif ($item->quantity > $stockAvailable) {
                    $item->update(['quantity' => $stockAvailable]); // Ajustar al máximo disponible
                }
            }
            
            // C. Refrescamos la instancia y recargamos relaciones
            $cart->refresh();
            $cart->load(['items.sku.product', 'items.sku.prices']);
        }
        // ---------------------------------------------

        // Si después de la limpieza quedó vacío
        if ($cart->items->isEmpty()) {
            return Inertia::render('Shop/Cart/Index', ['cart' => null, 'items' => []]);
        }

        // Transformación para la vista (ViewModel)
        $items = $cart->items->map(function ($item) use ($cart, $activeBranchId) {
            
            // Usamos precios y stock de la sucursal ACTUAL (ya migrada)
            $currentPrice = $item->sku->getCurrentPrice($activeBranchId);
            
            $realStock = InventoryLot::where('branch_id', $activeBranchId)
                ->where('sku_id', $item->sku_id)
                ->sum(DB::raw('quantity - reserved_quantity'));

            return [
                'id' => $item->id,
                'sku_id' => $item->sku_id,
                'name' => $item->sku->product->name . ' - ' . $item->sku->name,
                'image' => $item->sku->image_url,
                'quantity' => $item->quantity,
                'max_stock' => $realStock,
                'unit_price' => $currentPrice,
                'subtotal' => $currentPrice * $item->quantity,
                'stock_error' => $realStock < $item->quantity ? "Stock actual: {$realStock}" : null
            ];
        });

        return Inertia::render('Shop/Cart/Index', [
            'cart' => [
                'id' => $cart->id,
                'total' => $items->sum('subtotal'),
                'is_conflict' => false, // Ya resuelto automáticamente
                'branch_id' => $activeBranchId,
                'branch_name' => $cart->branch->name ?? 'Sucursal Actual'
            ],
            'items' => $items
        ]);
    }

    /**
     * Store usando la Arquitectura Correcta (Action + DTO)
     */
    public function store(
        AddToCartRequest $request, 
        AddItemToCartAction $action
    ) {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            $dto = AddToCartDTO::fromRequest($request, $branchId);
            $action->execute($dto);

            return back()->with('success', 'Producto agregado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $item = \App\Models\CartItem::with('cart')->findOrFail($id);
        
        $stockAvailable = InventoryLot::where('branch_id', $item->cart->branch_id)
            ->where('sku_id', $item->sku_id)
            ->sum(DB::raw('quantity - reserved_quantity'));

        if ($request->quantity > $stockAvailable) {
            return back()->withErrors(['error' => "Stock máximo: {$stockAvailable}"]);
        }

        $item->update(['quantity' => $request->quantity]);
        return back();
    }

    public function destroy($id)
    {
        \App\Models\CartItem::destroy($id);
        return back()->with('success', 'Producto eliminado.');
    }

    /**
     * Fusión de carritos (Invitado -> Usuario)
     */
    public static function mergeGuestCart(string $previousSessionId, $user)
    {
        $guestCart = Cart::where('session_id', $previousSessionId)->with('items')->first();

        if (!$guestCart) return;

        // Priorizamos la sucursal del usuario, si no tiene, la del carrito invitado
        $targetBranchId = $user->branch_id ?? $guestCart->branch_id;

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['branch_id' => $targetBranchId] 
        );

        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()->where('sku_id', $guestItem->sku_id)->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $guestItem->quantity);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Si hubo cambio de sucursal al loguear, alineamos el carrito
        if ($userCart->branch_id !== $targetBranchId) {
            $userCart->update(['branch_id' => $targetBranchId]);
        }

        $guestCart->delete(); 
    }
    public function bulkStore(
        BulkAddToCartRequest $request, 
        AddBulkItemsToCartAction $action
    ) {
        try {
            $branchId = $this->contextService->getActiveBranchId();
            
            $dto = BulkAddToCartDTO::fromRequest($request, $branchId);
            
            $action->execute($dto);

            return back()->with('success', 'Pack agregado al carrito correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}