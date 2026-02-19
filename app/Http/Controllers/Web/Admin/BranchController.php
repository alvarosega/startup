<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\DTOs\Admin\Branch\BranchData;
use App\Http\Requests\Admin\Branch\StoreBranchRequest;
use App\Http\Requests\Admin\Branch\UpdateBranchRequest;
use App\Actions\Admin\Branch\CreateBranch;
use App\Actions\Admin\Branch\UpdateBranch;
use App\Actions\Admin\Branch\ListBranches;
use App\Http\Resources\Admin\Branch\BranchResource;


class BranchController extends Controller
{
    public function index(ListBranches $action): Response
    {
        return Inertia::render('Admin/Branches/Index', [
            'branches' => BranchResource::collection($action->execute())->resolve()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Branches/Create');
    }

    public function store(StoreBranchRequest $request, CreateBranch $action): RedirectResponse
    {
        $action->execute(BranchData::fromRequest($request));

        return redirect()->route('admin.branches.index')
            ->with('message', 'Sucursal creada con éxito.');
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Admin/Branches/Edit', [
            'branch' => (new BranchResource($branch))->resolve()
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action): RedirectResponse
    {
        $action->execute($branch, BranchData::fromRequest($request));

        return redirect()->route('admin.branches.index')
            ->with('message', 'Sucursal actualizada con éxito.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        return redirect()->back()->with('message', 'Sucursal eliminada (Soft Delete).');
    }
}