<?php

namespace App\Http\Controllers\Web\Driver\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DTOs\Driver\Location\TelemetryDTO;
use App\Actions\Driver\Location\UpdateTelemetryAction;

class TelemetryController extends Controller
{
    public function update(Request $request, UpdateTelemetryAction $action)
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $driverId = Auth::guard('driver')->id();

        if (!$driverId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $dto = new TelemetryDTO(
                $driverId, 
                (float) $validated['latitude'], 
                (float) $validated['longitude']
            );
            
            $action->execute($dto);

            return response()->json(['status' => 'synced']);
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::critical('Fallo crítico en Telemetría: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo interno', 'message' => $e->getMessage()], 500);
        }
    }
}