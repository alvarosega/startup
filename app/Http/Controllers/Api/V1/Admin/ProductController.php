<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

use App\DTOs\Product\ProductData;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Actions\Product\CreateProduct;
use App\Actions\Product\UpdateProduct;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with(['brand', 'category', 'skus.prices'])->paginate(20);
        return response()->json(ProductResource::collection($products)->response()->getData(true));
    }

    public function store(StoreProductRequest $request, CreateProduct $action): JsonResponse
    {
        $data = ProductData::fromRequest($request);
        $product = $action->execute($data);
        return response()->json(new ProductResource($product), 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['brand', 'category', 'skus.prices']);
        return response()->json(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $action): JsonResponse
    {
        $data = ProductData::fromRequest($request);
        $product = $action->execute($product, $data);
        return response()->json(new ProductResource($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}