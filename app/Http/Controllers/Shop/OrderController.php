<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Historial de "Mis Pedidos"
     */
    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->withCount('items') // Para mostrar "X productos"
            ->paginate(10);

        return Inertia::render('Shop/Orders/History', ['orders' => $orders]);
    }

    /**
     * Detalle de Orden (Donde se sube el QR y se ve el estado)
     */
    public function show($code)
    {
        $order = Order::where('code', $code)
            ->where('user_id', Auth::id())
            ->with(['items.sku.product', 'branch'])
            ->firstOrFail();

        // Si la reserva expiró y no pagó, marcamos como cancelado automáticamente
        if ($order->status === 'pending_proof' && now()->greaterThan($order->reservation_expires_at)) {
            $order->update(['status' => 'cancelled', 'rejection_reason' => 'Tiempo de reserva agotado']);
        }

        return Inertia::render('Shop/Orders/Show', [
            'order' => $order,
            // Enviamos la URL pública de la imagen si existe
            'proofUrl' => $order->proof_of_payment ? Storage::url($order->proof_of_payment) : null
        ]);
    }

    /**
     * Subir el Comprobante de Pago
     */
    public function uploadProof(Request $request, $id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'proof' => 'required|image|max:4096' // Max 4MB
        ]);

        if ($order->status !== 'pending_proof') {
            return back()->withErrors(['error' => 'Esta orden ya no acepta comprobantes.']);
        }

        // Guardar imagen en disco público para que el Admin la vea fácil
        $path = $request->file('proof')->store("proofs/{$order->id}", 'public');

        $order->update([
            'proof_of_payment' => $path,
            'status' => 'review' // Cambiamos estado para que aparezca en el Kanban del Admin
        ]);

        return back()->with('success', 'Comprobante enviado. Estamos verificando tu pago.');
    }
}