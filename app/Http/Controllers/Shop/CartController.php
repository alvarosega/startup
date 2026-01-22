<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Sku;
use App\Models\InventoryLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Muestra el carrito actual con precios calculados en tiempo real.
     */
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request, false); // false = no crear si no existe

        if (!$cart) {
            return Inertia::render('Shop/Cart', [
                'cart' => null,
                'items' => []
            ]);
        }

        // Cargar ítems con la información necesaria para mostrar
        $cart->load(['items.sku.product.brand', 'items.sku.prices']);

        // Transformación para el Frontend (Calculamos precios aquí por seguridad)
        $items = $cart->items->map(function ($item) use ($cart) {
            $currentPrice = $item->sku->getCurrentPrice($cart->branch_id);
            
            // Validar stock una vez más por si se acabó mientras navegaba
            $realStock = InventoryLot::where('branch_id', $cart->branch_id)
                ->where('sku_id', $item->sku_id)
                ->sum('quantity');

            return [
                'id' => $item->id, // ID del item del carrito
                'sku_id' => $item->sku_id,
                'name' => $item->sku->name,
                'product_name' => $item->sku->product->name,
                'image' => $item->sku->image,
                'quantity' => $item->quantity,
                'max_stock' => $realStock,
                'unit_price' => $currentPrice,
                'subtotal' => $currentPrice * $item->quantity,
                'stock_warning' => $realStock < $item->quantity // Flag para alerta visual
            ];
        });

        return Inertia::render('Shop/Cart', [
            'cartId' => $cart->id,
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'branch_id' => $cart->branch_id
        ]);
    }

    /**
     * Agregar un ítem al carrito.
     * Valida estrictamente el stock de la sucursal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:skus,id',
            'quantity' => 'required|integer|min:1',
            'branch_id' => 'required|exists:branches,id', // Contexto obligatorio
        ]);

        try {
            DB::beginTransaction();

            // 1. Validar Stock Físico en la Sucursal
            $stockAvailable = InventoryLot::where('branch_id', $request->branch_id)
                ->where('sku_id', $request->sku_id)
                ->sum('quantity');

            if ($stockAvailable < $request->quantity) {
                return back()->withErrors(['quantity' => "Solo quedan {$stockAvailable} unidades en esta sucursal."]);
            }

            // 2. Obtener o Crear el Carrito (Contexto)
            $cart = $this->getOrCreateCart($request, true, $request->branch_id);

            // 3. Agregar o Actualizar el Ítem
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $request->sku_id)
                ->first();

            if ($cartItem) {
                // Si ya existe, sumamos. Pero validamos que la suma no exceda el stock.
                $newQty = $cartItem->quantity + $request->quantity;
                if ($newQty > $stockAvailable) {
                    return back()->withErrors(['quantity' => "No puedes agregar más. Stock límite alcanzado."]);
                }
                $cartItem->update(['quantity' => $newQty]);
            } else {
                // Crear nuevo item
                CartItem::create([
                    'cart_id' => $cart->id,
                    'sku_id' => $request->sku_id,
                    'quantity' => $request->quantity
                ]);
            }

            DB::commit();
            return back()->with('success', 'Producto agregado al carrito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar carrito: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar cantidad (desde la vista del carrito).
     */
    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $item = CartItem::with('cart')->findOrFail($id);
        
        // Validar Stock nuevamente
        $stockAvailable = InventoryLot::where('branch_id', $item->cart->branch_id)
            ->where('sku_id', $item->sku_id)
            ->sum('quantity');

        if ($request->quantity > $stockAvailable) {
            return back()->withErrors(['error' => "Stock insuficiente. Máximo disponible: {$stockAvailable}"]);
        }

        $item->update(['quantity' => $request->quantity]);

        return back(); // Inertia recargará y recalculará precios en el index()
    }

    /**
     * Eliminar ítem del carrito.
     */
    public function destroy($id)
    {
        CartItem::destroy($id);
        return back()->with('success', 'Producto eliminado.');
    }

    // --- HELPER PRIVADO --- //

    /**
     * Busca el carrito activo.
     * Lógica Híbrida: Guest (Session) vs User (Auth).
     * Regla Crítica: Si cambia de sucursal, se crea un carrito NUEVO (o se resetea).
     */
    private function getOrCreateCart(Request $request, $create = false, $targetBranchId = null)
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        $query = Cart::query();

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $sessionId);
        }

        // Si estamos intentando AGREGAR algo, filtramos por la sucursal objetivo
        if ($targetBranchId) {
            $query->where('branch_id', $targetBranchId);
        }

        $cart = $query->latest()->first();

        // VALIDACIÓN DE CONTEXTO (Solo al crear/agregar)
        // Si existe un carrito pero es de OTRA sucursal, tenemos 2 opciones:
        // A. Bloquear (Error)
        // B. Crear uno nuevo y abandonar el viejo (Elegimos B para fluidez, o podríamos limpiar el anterior)
        
        if (!$cart && $create && $targetBranchId) {
            // Opcional: Borrar carritos viejos de otras sucursales para este usuario/sesión para no dejar basura
            // Cart::where('session_id', $sessionId)->delete(); 

            $cart = Cart::create([
                'user_id' => $user ? $user->id : null,
                'session_id' => $user ? null : $sessionId,
                'branch_id' => $targetBranchId
            ]);
        }

        return $cart;
    }
}