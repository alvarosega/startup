<?php

namespace App\Http\Controllers\Web\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Driver;
use App\Services\Logistics\RedisLocationService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Añadido para telemetría interna

class MonitorController extends Controller
{
    public function __construct(
        protected RedisLocationService $redisService
    ) {}

    public function index()
    {
        $admin = Auth::guard('super_admin')->user();
        
        Log::info("=== INICIO DIAGNÓSTICO RADAR ADMIN ===");
        Log::info("Admin ID: {$admin->id}, Branch Asignada: " . ($admin->branch_id ?? 'Global'));

        // 1. Filtrar por la sucursal del Admin
        $branchQuery = Branch::query()->where('is_active', true);
        if ($admin->branch_id) {
            $branchQuery->where('id', $admin->branch_id);
        }
        $branches = $branchQuery->get(['id', 'name', 'latitude', 'longitude']);
        $branchIds = $branches->pluck('id');
        
        Log::info("Sucursales mapeadas para este Admin: " . json_encode($branchIds));

        // 2. Obtener órdenes activas
        $activeOrders = Order::whereIn('branch_id', $branchIds)
            ->whereIn('status', ['preparing', 'dispatched'])
            ->with(['customer:id,phone', 'driver:id,phone'])
            ->get(['id', 'code', 'status', 'branch_id', 'driver_id', 'delivery_type']);

        $drivers = Driver::whereIn('branch_id', $branchIds)
        ->with('details:driver_id,first_name,last_name,license_plate')
        ->get(['id', 'branch_id', 'phone', 'is_online']); // Aseguramos traer 'is_online'

        Log::info("Conductores que pasaron filtro SQL (Misma Branch + is_online=true): {$drivers->count()}");
        
        if ($drivers->count() > 0) {
            Log::info("IDs encontrados en SQL: " . json_encode($drivers->pluck('id')));
        } else {
            Log::warning("BLOQUEO SQL: Ningún conductor pasó. Verifica en MySQL que el conductor tenga branch_id válido y que is_online sea 1.");
        }

        // 4. Extraer ubicaciones actuales desde Redis
        $driverLocations = [];
        foreach ($drivers as $driver) {
            $location = $this->redisService->getLocation((string) $driver->id);
            if ($location) {
                $driverLocations[$driver->id] = [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'details' => $driver->details,
                    'phone' => $driver->phone,
                    'branch_id' => $driver->branch_id
                ];
                Log::info("REDIS HIT: Coordenada encontrada para Driver {$driver->id}");
            } else {
                Log::warning("REDIS MISS: El Driver {$driver->id} está online en SQL, pero no tiene coordenadas en Redis. (Llave expirada o no sincronizada)");
            }
        }

        Log::info("Conductores finales enviados al mapa Vue: " . count($driverLocations));
        Log::info("=== FIN DIAGNÓSTICO ===");

        return Inertia::render('Admin/Logistics/Monitor', [
            'branches' => $branches,
            'orders' => $activeOrders,
            'initialDrivers' => $driverLocations,
            'mapCenter' => ['lat' => -16.4897, 'lng' => -68.1193] 
        ]);
    }
}