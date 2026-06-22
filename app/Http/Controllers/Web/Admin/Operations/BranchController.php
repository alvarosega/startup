<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operations\Branch;
use App\DTOs\Admin\Operations\Branch\BranchData;
use App\Http\Requests\Admin\Operations\Branch\StoreBranchRequest;
use App\Http\Requests\Admin\Operations\Branch\UpdateBranchRequest;
use App\Actions\Admin\Operations\Branch\CreateBranch;
use App\Actions\Admin\Operations\Branch\UpdateBranch;
use App\Actions\Admin\Operations\Branch\ListBranches;
use App\Http\Resources\Admin\Operations\Branch\BranchResource;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function index(ListBranches $action): Response
    {
        // CORRECCIÓN: Apuntar al nuevo directorio operativo de vistas
        return Inertia::render('Admin/Operations/Branches/Index', [
            'branches' => BranchResource::collection($action->execute())->resolve()
        ]);
    }

    public function create(): Response
    {
        // CORRECCIÓN: Cambiar referencia de Workspace a Create
        return Inertia::render('Admin/Operations/Branches/Create');
    }

    public function store(StoreBranchRequest $request, CreateBranch $action): RedirectResponse
    {
        $action->execute(BranchData::fromRequest($request));

        return redirect()->route('admin.operations.branches.index')
            ->with('success', 'Sucursal operativa materializada con éxito.');
    }

    public function edit(Branch $branch): Response
    {
        // CORRECCIÓN: Cambiar referencia de Workspace a Edit
        return Inertia::render('Admin/Operations/Branches/Edit', [
            'branch' => (new BranchResource($branch))->resolve()
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action): RedirectResponse
    {
        $action->execute($branch, BranchData::fromRequest($request));

        return redirect()->route('admin.operations.branches.index')
            ->with('success', 'Parámetros logísticos de la sucursal actualizados.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        return redirect()->back()->with('success', 'Nodo de sucursal extraído de circulación (Soft Delete).');
    }
}