<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Price;

use App\Http\Controllers\Controller;
use App\Models\{Sku, Price};
use App\Actions\Admin\Price\{GetPricesBySkuAction, UpsertPriceAction, DeletePriceAction};
use App\DTOs\Admin\Price\PriceData;
use App\Http\Requests\Admin\Price\StorePriceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PriceController extends Controller
{
    private string $guard = 'super_admin';

    public function show(Sku $sku, GetPricesBySkuAction $action): JsonResponse
    {
        return response()->json([
            'prices' => $action->execute($sku)
        ]);
    }

    public function store(StorePriceRequest $request, UpsertPriceAction $action): JsonResponse
    {
        $adminId = (string) Auth::guard($this->guard)->id();
        $dto = PriceData::fromRequest($request, $adminId);

        $price = $action->execute($dto);

        return response()->json([
            'success' => true,
            'price'   => $price
        ]);
    }

    public function destroy(Price $price, DeletePriceAction $action): JsonResponse
    {
        $adminId = (string) Auth::guard($this->guard)->id();
        $action->execute($price, $adminId);
        
        return response()->json([
            'success' => true
        ]);
    }
}