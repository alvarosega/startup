<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Actions\Admin\Inventory\RegisterPurchaseIntakeAction;
use App\DTOs\Admin\Inventory\PurchaseIntakeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StorePurchaseRequest;
use App\Http\Resources\Admin\Inventory\PurchaseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PurchaseIntakeController extends Controller
{
    public function __invoke(
        StorePurchaseRequest $request, 
        RegisterPurchaseIntakeAction $action
    ): JsonResponse {
        $adminId = (string) Auth::id(); 
        
        $dto = PurchaseIntakeDTO::fromRequest($request->validated(), $adminId);
        $purchase = $action->execute($dto);

        return (new PurchaseResource($purchase))
            ->response()
            ->setStatusCode(201);
    }
}