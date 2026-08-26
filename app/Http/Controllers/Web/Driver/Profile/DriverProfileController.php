<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver\Profile;

use App\Actions\Driver\Profile\UploadDocsAction;
use App\DTOs\Driver\Profile\UploadDocsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Profile\UploadDocsRequest;
use App\Models\Users\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DriverProfileController extends Controller
{
    public function index(): Response
    {
        /** @var Driver $driver */
        $driver = Auth::guard('driver')->user();

        // Prevención de N+1
        $driver->loadMissing(['profile', 'branch']);

        $statusValue = is_object($driver->status) ? $driver->status->value : (string) $driver->status;

        return Inertia::render('Driver/Profile/Index', [
            'driver' => [
                'id'                 => (string) $driver->id,
                'name'               => $driver->profile 
                    ? trim("{$driver->profile->first_name} {$driver->profile->last_name}") 
                    : 'Driver',
                'first_name'         => $driver->profile?->first_name ?? '',
                'last_name'          => $driver->profile?->last_name ?? '',
                'email'              => $driver->email,
                'phone'              => $driver->phone,
                'status'             => $statusValue,
                'is_online'          => (bool) $driver->is_online,
                'is_available'       => (bool) $driver->is_available,
                'license_number'     => $driver->profile?->license_number ?? '',
                'license_plate'      => $driver->profile?->license_plate ?? '',
                'vehicle_type'       => $driver->profile?->vehicle_type ?? '',
                'rejection_reason'   => $driver->profile?->rejection_reason,
                'avatar_type'        => $driver->profile?->avatar_type ?? 'icon',
                'avatar_source'      => $driver->profile?->avatar_source ?? 'avatar_1.svg',
                'ci_front_path'      => $driver->profile?->ci_front_path,
                'license_photo_path' => $driver->profile?->license_photo_path,
                'vehicle_photo_path' => $driver->profile?->vehicle_photo_path,
                'branch'             => $driver->branch ? [
                    'id'   => (string) $driver->branch->id,
                    'name' => (string) $driver->branch->name,
                ] : null,
            ],
        ]);
    }

    public function uploadDocs(UploadDocsRequest $request, UploadDocsAction $action): RedirectResponse
    {
        /** @var Driver $driver */
        $driver = Auth::guard('driver')->user();

        $statusValue = is_object($driver->status) ? $driver->status->value : (string) $driver->status;

        // Bloqueo de seguridad: Inmutabilidad post-aprobación
        if ($statusValue === 'approved') {
            return back()->with('error', 'Operación denegada. Tu cuenta ya está operativa y los documentos no pueden ser alterados.');
        }

        $data = UploadDocsData::fromRequest($request);
        $action->execute($data, $driver);

        return back()->with('success', 'Documentos enviados a revisión.');
    }
}