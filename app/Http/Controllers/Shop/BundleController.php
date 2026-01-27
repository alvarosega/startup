<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\InventoryLot;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\DB;

class BundleController extends Controller
{
    protected $contextService;

    public function __construct(ShopContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function show($slug)
    {
        $branchId = $this->contextService->getActiveBranchId();

        $bundle = Bundle::with(['skus.product', 'skus.prices'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Transformamos los items calculando stock real y precio local
        $items = $bundle->skus->map(function ($sku) use ($branchId) {
            
            $currentPrice = $sku->getCurrentPrice($branchId);

            $realStock = InventoryLot::where('branch_id', $branchId)
                ->where('sku_id', $sku->id)
                ->sum(DB::raw('quantity - reserved_quantity'));

            return [
                'sku_id' => $sku->id,
                'name' => $sku->product->name . ' ' . $sku->name,
                // Asegúrate que tu modelo SKU tenga este accessor o columna
                'image' => $sku->image_url ?? '/images/placeholder.png', 
                'default_quantity' => $sku->pivot->quantity,
                'unit_price' => $currentPrice,
                'max_stock' => $realStock,
            ];
        });

        return response()->json([
            'bundle' => [
                'id' => $bundle->id,
                'name' => $bundle->name,
                'description' => $bundle->description,
            ],
            'items' => $items,
        ]);
    }
}