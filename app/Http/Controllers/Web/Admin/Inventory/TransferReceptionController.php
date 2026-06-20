<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Actions\Admin\Inventory\ProcessTransferReceptionAction;
use App\DTOs\Admin\Inventory\TransferIntakeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\ReceiveTransferRequest;
use App\Http\Resources\Admin\Inventory\TransferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransferReceptionController extends Controller
{
    public function __invoke(
        ReceiveTransferRequest $request, 
        string $id, 
        ProcessTransferReceptionAction $action
    ): JsonResponse {
        $adminId = (string) Auth::id();

        $dto = TransferIntakeDTO::fromRequest($id, $adminId, $request->validated());
        $transfer = $action->execute($dto);

        return (new TransferResource($transfer))
            ->response()
            ->setStatusCode(200);
    }
}