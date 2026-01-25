<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

use App\DTOs\Branch\BranchData;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use App\Http\Resources\BranchResource;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::all();
        return response()->json(BranchResource::collection($branches));
    }

    public function store(StoreBranchRequest $request, CreateBranch $action): JsonResponse
    {
        $data = BranchData::fromRequest($request);
        $branch = $action->execute($data);
        return response()->json(new BranchResource($branch), 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        return response()->json(new BranchResource($branch));
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action): JsonResponse
    {
        $data = BranchData::fromRequest($request);
        $branch = $action->execute($branch, $data);
        return response()->json(new BranchResource($branch));
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();
        return response()->json(['message' => 'Branch deleted']);
    }
}