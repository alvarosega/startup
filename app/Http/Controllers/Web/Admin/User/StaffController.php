<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\User;

use App\Actions\Admin\Users\Staff\CreateStaffAction;
use App\Actions\Admin\Users\Staff\GetStaffFormOptionsAction;
use App\Actions\Admin\Users\Staff\ListStaffAction;
use App\Actions\Admin\Users\Staff\ToggleStaffStatusAction;
use App\Actions\Admin\Users\Staff\UpdateStaffAction;
use App\DTOs\Admin\User\StaffData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\Staff\StoreStaffRequest;
use App\Http\Requests\Admin\Users\Staff\UpdateStaffRequest;
use App\Models\Users\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class StaffController extends Controller
{
    public function index(Request $request, ListStaffAction $action, GetStaffFormOptionsAction $optionsAction): InertiaResponse
    {
        $filters = $request->only(['search', 'branch_id', 'role', 'is_active']);
        $staff = $action->execute($filters);
        $options = $optionsAction->execute();

        return Inertia::render('Admin/Users/Staff/Index', [
            'staff'    => $staff,
            'branches' => $options['branches'],
            'roles'    => $options['roles'],
            'filters'  => $filters,
        ]);
    }

    public function create(GetStaffFormOptionsAction $optionsAction): InertiaResponse
    {
        return Inertia::render('Admin/Users/Staff/Create', [
            'options' => $optionsAction->execute(),
        ]);
    }

    public function store(StoreStaffRequest $request, CreateStaffAction $action): RedirectResponse
    {
        $dto = StaffData::fromStoreRequest($request);
        $action->execute($dto);

        return redirect()->route('staff.index')
            ->with('message', 'Operador registrado y roles asignados con éxito.');
    }

    public function edit(Admin $admin, GetStaffFormOptionsAction $optionsAction): InertiaResponse
    {
        $admin->load(['branch', 'roles', 'permissions']);

        return Inertia::render('Admin/Users/Staff/Edit', [
            'operator' => [
                'id'          => (string) $admin->id,
                'first_name'  => (string) $admin->first_name,
                'last_name'   => (string) $admin->last_name,
                'email'       => (string) $admin->email,
                'phone'       => $admin->phone,
                'branch_id'   => $admin->branch_id,
                'is_active'   => (bool) $admin->is_active,
                'roles'       => $admin->getRoleNames()->values()->all(),
                'permissions' => $admin->getPermissionNames()->values()->all(),
            ],
            'options' => $optionsAction->execute(),
        ]);
    }

    public function update(UpdateStaffRequest $request, Admin $admin, UpdateStaffAction $action): RedirectResponse
    {
        $dto = StaffData::fromUpdateRequest($request);
        $action->execute($admin, $dto);

        return redirect()->route('staff.index')
            ->with('message', 'Operador actualizado correctamente.');
    }

    public function toggleStatus(Admin $admin, ToggleStaffStatusAction $action): RedirectResponse
    {
        $newStatus = $action->execute($admin);
        $statusLabel = $newStatus ? 'activado' : 'suspendido';

        return back()->with('message', "El operador ha sido {$statusLabel}.");
    }
}
