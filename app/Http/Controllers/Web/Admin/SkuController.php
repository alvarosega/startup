<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sku;
use App\Http\Requests\Admin\Sku\{StoreSkuRequest, UpdateSkuRequest};
use App\DTOs\Admin\Sku\UpdateSkuDTO;
use App\Actions\Admin\Sku\{CreateSkuAction, UpdateSkuAction, DeleteSkuAction};
use Illuminate\Http\RedirectResponse;
use App\Models\Product; // <--- ESTA ES LA IMPORTACIÓN QUE FALTA
use App\Http\Requests\Admin\Sku\StoreBulkSkuRequest;
use App\Actions\Admin\Sku\CreateBulkSkuAction;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\DTOs\Admin\Sku\CreateBulkSkuDTO; // <--- IMPORTACIÓN CRÍTICA


class SkuController extends Controller
{
    public function create(Product $product): InertiaResponse
    {
        return Inertia::render('Admin/Skus/Create', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name
            ]
        ]);
    }
    
    public function store(StoreBulkSkuRequest $request, Product $product, CreateBulkSkuAction $action): RedirectResponse
    {
        // Ahora usamos el DTO de forma segura
        $action->execute($product->id, CreateBulkSkuDTO::fromRequest($request));

        return redirect()->route('admin.products.index')
            ->with('success', 'Variantes creadas y catálogo finalizado.');
    }
    public function edit(Sku $sku): InertiaResponse
    {
        $sku->load('product');
    
        return Inertia::render('Admin/Skus/Edit', [
            'sku' => $sku,
            'product' => $sku->product
        ]);
    }
    public function update(UpdateSkuRequest $request, Sku $sku, UpdateSkuAction $action): RedirectResponse
    {
        $action->execute($sku, $request->validated());
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Variante actualizada correctamente.');
    }

    public function destroy(Sku $sku, DeleteSkuAction $action): RedirectResponse
    {
        $action->execute($sku);

        return back()->with('warning', 'Variante eliminada del catálogo.');
    }

}