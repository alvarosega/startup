<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Sku;

use App\Http\Controllers\Controller;
use App\Models\{Sku, Product};
use App\Http\Requests\Admin\Sku\{StoreBulkSkuRequest, UpdateSkuRequest};
use App\DTOs\Admin\Sku\{CreateBulkSkuDTO, SkuDataDTO};
use App\Actions\Admin\Sku\{CreateBulkSkuAction, UpdateSkuAction, DeleteSkuAction};
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SkuController extends Controller
{
    use AuthorizesRequests;

    /**
     * Procesa la inserción masiva de SKUs desde la pestaña correspondiente del Workspace.
     */
    public function store(StoreBulkSkuRequest $request, Product $product, CreateBulkSkuAction $action): RedirectResponse
    {
        $this->authorize('create', Sku::class);

        $dto = CreateBulkSkuDTO::fromRequest($request);
        $action->execute($product->id, $dto->skus);

        // Retorna un redireccionamiento inyectado hacia atrás para recargar las propiedades del Workspace de forma asíncrona
        return redirect()->back()->with('success', 'Variantes físicas vinculadas correctamente.');
    }

    /**
     * Actualiza los parámetros de una presentación específica de forma atómica.
     */
    public function update(UpdateSkuRequest $request, Sku $sku, UpdateSkuAction $action): RedirectResponse
    {
        $this->authorize('update', $sku);
        
        $action->execute($sku, SkuDataDTO::fromRequest($request)); 
    
        return redirect()->back()->with('success', 'Parámetros de la variante sincronizados.');
    }

    /**
     * Remueve lógicamente una variante sin destruir el resto del Workspace.
     */
    public function destroy(Sku $sku, DeleteSkuAction $action): RedirectResponse
    {
        $this->authorize('delete', $sku);
        $action->execute($sku);

        return redirect()->back()->with('success', 'Variante extraída de circulación de forma permanente.');
    }
}