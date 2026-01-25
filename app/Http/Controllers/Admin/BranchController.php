<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\DTOs\Branch\BranchData;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use App\Http\Resources\BranchResource;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // $this->authorize('viewAny', Branch::class);
        $branches = Branch::orderBy('id', 'desc')->get();

        return Inertia::render('Admin/Branches/Index', [
            'branches' => BranchResource::collection($branches)->resolve()
        ]);
    }

    public function create()
    {
        // $this->authorize('create', Branch::class);
        return Inertia::render('Admin/Branches/Create');
    }

    public function store(StoreBranchRequest $request, CreateBranch $action)
    {
        // $this->authorize('create', Branch::class);
        $data = BranchData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.branches.index')->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Branch $branch)
    {
        // $this->authorize('update', $branch);
        return Inertia::render('Admin/Branches/Edit', [
            'branch' => (new BranchResource($branch))->resolve()
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action)
    {
        // $this->authorize('update', $branch);
        $data = BranchData::fromRequest($request);
        $action->execute($branch, $data);

        return redirect()->route('admin.branches.index')->with('success', 'Sucursal actualizada correctamente.');
    }
}