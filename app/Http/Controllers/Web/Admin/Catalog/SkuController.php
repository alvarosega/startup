<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Sku;
use App\Models\Catalog\Product;
use App\Http\Requests\Admin\Catalog\Sku\StoreBulkSkuRequest;
use App\Http\Requests\Admin\Catalog\Sku\UpdateSkuRequest;
use App\DTOs\Admin\Catalog\Sku\CreateBulkSkuDTO;
use App\DTOs\Admin\Catalog\Sku\SkuDataDTO;
use App\Actions\Admin\Catalog\Sku\CreateBulkSkuAction;
use App\Actions\Admin\Catalog\Sku\UpdateSkuAction;
use App\Actions\Admin\Catalog\Sku\DeleteSkuAction;
use Illuminate\Http\RedirectResponse;

class SkuController extends Controller
{
    /**
     * Procesa la inserción masiva de SKUs desde el Workspace de trabajo.
     */
    public function store(StoreBulkSkuRequest $request, Product $product, CreateBulkSkuAction $action): RedirectResponse
    {
        $dto = CreateBulkSkuDTO::fromRequest($request);
        $action->execute($product->id, $dto->skus);

        return redirect()->back()->with('success', 'Variantes físicas vinculadas correctamente.');
    }

    /**
     * Actualiza los parámetros de una presentación comercial de forma atómica.
     */
    public function update(UpdateSkuRequest $request, Sku $sku, UpdateSkuAction $action): RedirectResponse
    {
        $action->execute($sku, SkuDataDTO::fromRequest($request)); 
    
        return redirect()->back()->with('success', 'Parámetros de la variante sincronizados.');
    }

    /**
     * Remueve lógicamente una variante sin alterar el Workspace de trabajo general.
     */
    public function destroy(Sku $sku, DeleteSkuAction $action): RedirectResponse
    {
        $action->execute($sku);

        return redirect()->back()->with('success', 'Variante extraída de circulación de forma permanente.');
    }
}