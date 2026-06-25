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

use App\Http\Resources\Admin\Users\Driver\DriverResource;

class DriverController extends Controller
{
    public function index(Request $request, GetDriversListAction $action): InertiaResponse
    {
        $payload = $request->only(['search', 'branch_id', 'status']);
        $paginator = $action->execute($payload);

        return Inertia::render('Admin/Users/Drivers/Index', [
            'users' => DriverResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'filters' => $payload
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Users/Drivers/Create', [
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
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

    public function searchDeleted(Request $request, SearchDeletedDriverAction $action): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);
        $driver = $action->execute($request->input('phone'));

        if (!$driver) {
            return response()->json(['message' => 'No se encontró ningún repartidor eliminado con ese teléfono.'], 404);
        }

        return response()->json(['user' => new DriverResource($driver)]);
    }

    public function restoreDeleted(Request $request, string $id, RestoreDriverAction $action): RedirectResponse
    {
        $context = AuditContext::fromRequest($request);
        $action->execute($id, $context);

        return redirect()->route('drivers.index')
            ->with('message', 'Cuenta de repartidor restaurada en estado "pending".');
    }
}