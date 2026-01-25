<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\InventoryLot;

use App\DTOs\Price\PriceData;
use App\Http\Requests\Price\UpdatePriceRequest;
use App\Actions\Price\UpdateBranchPrice;

class PriceController extends Controller
{
    public function index(): JsonResponse
    {
        // Retornamos stock plano para API, sin agrupar visualmente
        $stock = InventoryLot::with(['branch', 'sku.prices'])
            ->where('quantity', '>', 0)
            ->distinct()
            ->paginate(50);
            
        return response()->json($stock);
    }

    public function store(UpdatePriceRequest $request, UpdateBranchPrice $action): JsonResponse
    {
        $data = PriceData::fromRequest($request);
        $action->execute($data);
        return response()->json(['message' => 'Price updated successfully']);
    }
}