<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Logistics\RedisLocationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::guard('driver')->user();
        
        // Cargamos los detalles y la sucursal para extraer sus coordenadas
        $driver->load(['details', 'branch']);

        // Blindaje SQL: Solo pedidos 'preparing', sin conductor asignado y estrictamente 'delivery'
        $pendingOrders = Order::where('branch_id', $driver->branch_id)
            ->where('status', 'preparing')
            ->whereNull('driver_id')
            ->where('delivery_type', 'delivery') 
            ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'total_amount']);

        return Inertia::render('Driver/Dashboard', [
            'driver' => [
                'id' => $driver->id,
                'status' => $driver->status,
                'is_online' => (bool) $driver->is_online,
                'has_documents' => $this->checkDocuments($driver->details),
            ],
            // Inyectamos el nodo central (Sucursal)
            'branch' => $driver->branch ? [
                'name' => $driver->branch->name,
                'lat'  => $driver->branch->latitude,
                'lng'  => $driver->branch->longitude,
            ] : null,
            'pendingOrders' => $pendingOrders
        ]);
    }

    /**
     * Endpoint para alternar el estado del turno (Encendido / Apagado)
     */
    public function toggleStatus(Request $request, RedisLocationService $redis)
    {
        $driver = Auth::guard('driver')->user();
        $isOnline = $request->boolean('is_online');

        $driver->update(['is_online' => $isOnline]);

        if (!$isOnline) {
            $redis->removeLocation((string) $driver->id);
        }

        return redirect()->back();
    }

    private function checkDocuments($details)
    {
        return !empty($details->ci_front_path) && !empty($details->license_photo_path);
    }
}