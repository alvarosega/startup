<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id', 1);
        
        $branch = Branch::where('id', $branchId)->where('is_active', true)->first();
        if (!$branch) {
            $branchId = 1;
            $branch = Branch::find(1);
        }

        $products = Product::whereHas('skus.inventoryLots', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->where('quantity', '>', 0);
            })
            ->where('is_active', true)
            ->with([
                'brand',
                'category',
                'skus' => function ($q) use ($branchId) {
                    $q->where('is_active', true)
                      ->with([
                        'prices' => function ($p) use ($branchId) {
                            $p->where('branch_id', $branchId)
                              ->orWhereNull('branch_id')
                              ->orderByRaw('branch_id IS NOT NULL DESC');
                        }
                    ]);
                }
            ])
            ->get()
            ->map(function ($product) use ($branchId) {
                
                $product->available_skus = $product->skus->map(function ($sku) use ($branchId) {
                    
                    $stock = $sku->inventoryLots()
                        ->where('branch_id', $branchId)
                        ->sum('quantity');

                    if ($stock <= 0) return null;

                    $activePriceObj = $sku->prices->first();
                    $finalPrice = $activePriceObj ? (float)$activePriceObj->final_price : 0;

                    return [
                        'id' => $sku->id,
                        'name' => $sku->name,
                        'code' => $sku->code,
                        'price' => $finalPrice,
                        'stock' => (float)$stock,
                        'weight' => $sku->weight,
                        'image' => $sku->image, 
                    ];
                })
                ->filter()
                ->values(); // <--- ¡CRÍTICO! Esto convierte el Objeto JSON de vuelta a Array

                return $product;
            })
            ->filter(fn($p) => $p->available_skus->isNotEmpty())
            ->values(); // <--- ¡CRÍTICO! Re-indexar los productos también

        return Inertia::render('Shop/Index', [
            'products' => $products, // Ya es un array limpio
            'currentBranch' => $branch,
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ]);
    }
}