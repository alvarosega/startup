<?php

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\DTOs\Admin\Inventory\Stock\StockFilterDTO;
use App\Actions\Admin\Inventory\Stock\GetConsolidatedStockAction;
use App\Http\Resources\Admin\Inventory\Stock\InventoryResource;

use App\Actions\Admin\Inventory\Purchase\GetPurchaseFormDataAction; // Reutilizamos para el selector de sucursales
use App\DTOs\Admin\Inventory\Stock\KardexFilterDTO;
use App\Actions\Admin\Inventory\Stock\GetStockFilterDataAction;
use App\Actions\Admin\Inventory\Stock\GetKardexAction;
use App\Http\Resources\Admin\Inventory\Stock\InventoryMovementResource;
use App\Models\Sku;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request, 
        GetConsolidatedStockAction $action,
        GetStockFilterDataAction $filterDataAction // Usamos el nuevo action
    ) {
        $user = auth('super_admin')->user();
        $dto = StockFilterDTO::fromRequest($request);
        
        $stock = $action->execute($dto, $user->branch_id);
        $filterOptions = $filterDataAction->execute($user->branch_id);

        return Inertia::render('Admin/Inventory/Stock/Index', array_merge([
            'stock'   => InventoryResource::collection($stock),
            'filters' => [
                'search'      => $dto->search,
                'branch_id'   => $dto->branch_id,
                'provider_id' => $dto->provider_id,
                'brand_id'    => $dto->brand_id,
                'category_id' => $dto->category_id,
                'status'      => $dto->status,
            ],
        ], $filterOptions)); // Merge inyecta branches, providers, brands, categories
    }
    public function kardex(
        Request $request, 
        string $skuId, 
        GetKardexAction $action,
        GetPurchaseFormDataAction $formDataAction
    ) {
        $user = auth('super_admin')->user();
        
        // Verificación plana de existencia
        $sku = Sku::with('product:id,name')->findOrFail($skuId);

        $dto = KardexFilterDTO::fromRequest($request, $skuId);
        $movements = $action->execute($dto, $user->branch_id);

        return Inertia::render('Admin/Inventory/Stock/Kardex', [
            'sku'       => [
                'id'        => $sku->id,
                'name'      => $sku->name,
                'code'      => $sku->code,
                'product'   => $sku->product->name ?? 'N/A'
            ],
            'movements' => InventoryMovementResource::collection($movements),
            'filters'   => [
                'branch_id'  => $dto->branch_id,
                'start_date' => $dto->start_date,
                'end_date'   => $dto->end_date,
            ],
            'branches'  => $user->branch_id ? [] : $formDataAction->getBranchesForFilter()
        ]);
    }
}