<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

use App\DTOs\Brand\BrandData;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Actions\Brand\CreateBrand;
use App\Actions\Brand\UpdateBrand;
use App\Http\Resources\BrandResource;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::with(['provider', 'categories'])->paginate(20);
        return response()->json(BrandResource::collection($brands)->response()->getData(true));
    }

    public function store(StoreBrandRequest $request, CreateBrand $action): JsonResponse
    {
        $data = BrandData::fromRequest($request);
        $brand = $action->execute($data);
        return response()->json(new BrandResource($brand), 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        $brand->load(['provider', 'categories']);
        return response()->json(new BrandResource($brand));
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpdateBrand $action): JsonResponse
    {
        $data = BrandData::fromRequest($request);
        $brand = $action->execute($brand, $data);
        return response()->json(new BrandResource($brand));
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();
        return response()->json(['message' => 'Brand deleted']);
    }
}