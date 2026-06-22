<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Price;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Price; // RECTIFICACIÓN: Namespace modular correcto
use App\Models\Catalog\Sku;
use App\Actions\Admin\Price\{GetPricesBySkuAction, UpsertPriceAction, DeletePriceAction};
use App\DTOs\Admin\Inventory\Price\PriceData; // RECTIFICACIÓN: Sincronizado con el silo de inventario
use App\Http\Requests\Admin\Inventory\Price\StorePriceRequest; // RECTIFICACIÓN: Sincronizado con el silo de inventario
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
        
        // Sincronizado con la firma del DTO que lee el request de inventario
        $dto = PriceData::fromRequest($request); 

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