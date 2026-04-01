<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Sku;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Sku\GetSkuStateRequest;
use App\Actions\Customer\Sku\GetSkuStateAction;
use App\Http\Resources\Customer\Sku\SkuStateResource;
use Illuminate\Http\JsonResponse;

class SkuController extends Controller
{
    public function state(GetSkuStateRequest $request, GetSkuStateAction $action): JsonResponse
    {
        $qtyInCart = (int) $request->validated('qty_in_cart', 0);
        $dto = $action->execute($request->validated('sku_id'), $qtyInCart);

        return response()->json(new SkuStateResource($dto));
    }
}