<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Purchase\StorePurchaseRequest;
use App\DTOs\Admin\Inventory\Purchase\PurchaseData;
use App\Actions\Admin\Inventory\Purchase\ProcessPurchaseIntake;
use Illuminate\Http\RedirectResponse;

final class PurchaseIntakeController extends Controller
{
    /**
     * Controlador Invocable Atómico para Ingesta Logística.
     */
    public function __invoke(StorePurchaseRequest $request, ProcessPurchaseIntake $action): RedirectResponse
    {
        $action->execute(PurchaseData::fromRequest($request));

        return redirect()->route('admin.purchases.index')
            ->with('success', 'PROTOCOLO_RECEPCIÓN: Lote físico verificado e integrado a las existencias centrales.');
    }
}