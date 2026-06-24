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

use App\Http\Requests\Admin\Users\Customer\StoreCustomerRequest;
use App\Http\Requests\Admin\Users\Customer\ChangeCustomerStatusRequest;

use App\DTOs\Admin\Users\Customer\StoreCustomerDTO;
use App\DTOs\Admin\Users\Customer\ChangeCustomerStatusDTO;
use App\DTOs\Admin\Users\AuditContext;

use App\Actions\Admin\Users\Customer\GetCustomersListAction;
use App\Actions\Admin\Users\Customer\StoreCustomerAction;
use App\Actions\Admin\Users\Customer\ChangeCustomerStatusAction;
use App\Actions\Admin\Users\Customer\SearchDeletedCustomerAction;
use App\Actions\Admin\Users\Customer\RestoreCustomerAction;

use App\Http\Resources\Admin\Users\Customer\CustomerResource;

class CustomerController extends Controller
{
    public function index(Request $request, GetCustomersListAction $action): InertiaResponse
    {
        $payload = $request->only(['search', 'branch_id', 'is_active']);
        $paginator = $action->execute($payload);

        return Inertia::render('Admin/Users/Customers/Index', [
            'users' => CustomerResource::collection($paginator->items()),
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
        return Inertia::render('Admin/Users/Customers/Create', [
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ]);
    }

    public function store(StoreCustomerRequest $request, StoreCustomerAction $action): RedirectResponse
    {
        $dto = StoreCustomerDTO::fromRequest($request);
        $context = AuditContext::fromRequest($request);

        $action->execute($dto, $context);

        return redirect()->route('admin.users.customers.index')
            ->with('message', 'Cliente registrado con contraseña provisional.');
    }

    public function changeStatus(ChangeCustomerStatusRequest $request, string $id, ChangeCustomerStatusAction $action): RedirectResponse
    {
        $dto = ChangeCustomerStatusDTO::fromRequest($request, $id);
        $context = AuditContext::fromRequest($request);

        $action->execute($dto, $context);

        return redirect()->route('admin.users.customers.index')
            ->with('message', 'Estado del cliente actualizado de forma segura.');
    }

    public function searchDeleted(Request $request, SearchDeletedCustomerAction $action): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);
        $customer = $action->execute($request->input('phone'));

        if (!$customer) {
            return response()->json(['message' => 'No se encontró ningún usuario eliminado con ese teléfono.'], 404);
        }

        return response()->json(['user' => new CustomerResource($customer)]);
    }

    public function restoreDeleted(Request $request, string $id, RestoreCustomerAction $action): RedirectResponse
    {
        $context = AuditContext::fromRequest($request);
        $action->execute($id, $context);

        return redirect()->route('admin.users.customers.index')
            ->with('message', 'Cuenta de cliente restaurada en modo inactivo para revisión.');
    }
}