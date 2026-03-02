<?php

namespace App\Http\Controllers\Web\Driver\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Logistics\RedisLocationService;
use App\Events\DriverLocationUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelemetryController extends Controller
{
    public function __construct(
        protected RedisLocationService $redisService
    ) {}

    public function update(Request $request)
    {
        try {
            // 1. Validación
            $validated = $request->validate([
                'latitude'  => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ]);

            $driver = Auth::guard('driver')->user();

            if (!$driver) {
                Log::warning('Telemetría denegada: Conductor no autenticado.');
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            Log::info("Iniciando telemetría para Driver: {$driver->id}");
            if (!$driver->is_online) {
                $driver->update(['is_online' => true]);
                Log::info("Estado MySQL corregido: Driver {$driver->id} marcado como ONLINE.");
            }
            Log::info("Iniciando telemetría para Driver: {$driver->id}");
            // 2. Persistencia en Redis
            $this->redisService->updateLocation(
                (string) $driver->id,
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
            
            Log::info("Redis actualizado correctamente para Driver: {$driver->id}");

            // 3. Emisión WebSocket
            broadcast(new DriverLocationUpdated(
                (string) $driver->id,
                (float) $validated['latitude'],
                (float) $validated['longitude']
            ))->toOthers();

            Log::info("Broadcast emitido correctamente para Driver: {$driver->id}");

            return response()->json(['status' => 'synced']);

        } catch (\Throwable $e) {
            // 4. Captura Quirúrgica de Errores (Falla de DB, Redis, Pusher, Sintaxis, etc.)
            Log::critical('Fallo crítico en Telemetría', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ]);

            return response()->json([
                'error'   => 'Fallo interno del servidor',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}