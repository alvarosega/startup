<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

use App\DTOs\Category\CategoryData;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Actions\Category\CreateCategory;
use App\Actions\Category\UpdateCategory;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('parent')->get();
        return response()->json(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action): JsonResponse
    {
        $data = CategoryData::fromRequest($request);
        $category = $action->execute($data);
        return response()->json(new CategoryResource($category), 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $action): JsonResponse
    {
        $data = CategoryData::fromRequest($request);
        $category = $action->execute($category, $data);
        return response()->json(new CategoryResource($category));
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->exists()) {
            return response()->json(['error' => 'Category has children'], 409);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}