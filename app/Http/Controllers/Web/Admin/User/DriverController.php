<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Operations\Branch;
use App\Models\Users\Driver;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\Admin\Users\Driver\StoreDriverRequest;
use App\Http\Requests\Admin\Users\Driver\ChangeDriverStatusRequest;

use App\DTOs\Admin\Users\Driver\StoreDriverDTO;
use App\DTOs\Admin\Users\Driver\ChangeDriverStatusDTO;
use App\DTOs\Admin\Users\AuditContext;

use App\Actions\Admin\Users\Driver\GetDriversListAction;
use App\Actions\Admin\Users\Driver\StoreDriverAction;
use App\Actions\Admin\Users\Driver\ChangeDriverStatusAction;
use App\Actions\Admin\Users\Driver\SearchDeletedDriverAction;
use App\Actions\Admin\Users\Driver\RestoreDriverAction;

class DriverController extends Controller
{
    public function index(Request $request, GetDriversListAction $action): InertiaResponse
    {
        $payload = $request->only(['search', 'branch_id', 'status']);
        $drivers = $action->execute($payload);

        return Inertia::render('Admin/Users/Drivers/Index', [
            'drivers'  => $drivers,
            'branches' => Branch::where('is_active', true)->where('deleted_epoch', 0)->orderBy('name')->get(['id', 'name']),
            'filters'  => $payload,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Users/Drivers/Create', [
            'branches' => Branch::where('is_active', true)->where('deleted_epoch', 0)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreDriverRequest $request, StoreDriverAction $action): RedirectResponse
    {
        $dto = StoreDriverDTO::fromRequest($request);
        $context = AuditContext::fromRequest($request);

        $action->execute($dto, $context);

        return redirect()->route('drivers.index')
            ->with('message', 'Repartidor registrado de forma completa.');
    }

    public function changeStatus(ChangeDriverStatusRequest $request, string $id, ChangeDriverStatusAction $action): RedirectResponse
    {
        $dto = ChangeDriverStatusDTO::fromRequest($request, $id);
        $context = AuditContext::fromRequest($request);

        $action->execute($dto, $context);

        return redirect()->route('drivers.index')
            ->with('message', 'Estado del repartidor e integridad de sesión actualizados.');
    }

    public function viewDocument(string $id, string $type)
    {
        $driver = Driver::with('profile')->findOrFail($id);
        $profile = $driver->profile;

        if (!$profile) {
            abort(404, 'Perfil de conductor no encontrado.');
        }

        $path = match ($type) {
            'ci'      => $profile->ci_front_path,
            'license' => $profile->license_photo_path,
            'vehicle' => $profile->vehicle_photo_path,
            default   => abort(400, 'Tipo de documento no válido.')
        };

        if (!$path) {
            abort(404, 'Documento no cargado.');
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        if (file_exists(storage_path('app/' . $path))) {
            return response()->file(storage_path('app/' . $path));
        }

        abort(404, 'Archivo físico no encontrado en almacenamiento.');
    }

    public function searchDeleted(Request $request, SearchDeletedDriverAction $action): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);
        $driver = $action->execute($request->input('phone'));

        if (!$driver) {
            return response()->json(['message' => 'No se encontró ningún repartidor eliminado con ese teléfono.'], 404);
        }

        $profile = $driver->profile;
        return response()->json([
            'user' => [
                'id'            => (string) $driver->id,
                'email'         => (string) $driver->email,
                'phone'         => (string) $driver->phone,
                'status'        => is_object($driver->status) ? $driver->status->value : (string) $driver->status,
                'full_name'     => trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: 'Sin nombre',
                'license_plate' => $profile?->license_plate,
                'vehicle_type'  => $profile?->vehicle_type,
            ]
        ]);
    }

    public function restoreDeleted(Request $request, string $id, RestoreDriverAction $action): RedirectResponse
    {
        $context = AuditContext::fromRequest($request);
        $action->execute($id, $context);

        return redirect()->route('drivers.index')
            ->with('message', 'Cuenta de repartidor restaurada en estado "pending".');
    }
}
