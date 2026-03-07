<?php
namespace App\Http\Controllers\Web\Admin\Sku;

use App\Http\Controllers\Controller;
use App\Models\{Sku, Product};
use App\Http\Requests\Admin\Sku\{StoreBulkSkuRequest, UpdateSkuRequest};
use App\DTOs\Admin\Sku\{CreateBulkSkuDTO, SkuDataDTO};
use App\Actions\Admin\Sku\{CreateBulkSkuAction, UpdateSkuAction, DeleteSkuAction};
use Illuminate\Http\RedirectResponse;
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SkuController extends Controller
{
    use AuthorizesRequests;

    public function create(Product $product): InertiaResponse
    {
        $this->authorize('create', Sku::class); // Si tienes SkuPolicy
        return Inertia::render('Admin/Skus/Create', [
            'product' => ['id' => $product->id, 'name' => $product->name]
        ]);
    }
    
    public function store(StoreBulkSkuRequest $request, Product $product, CreateBulkSkuAction $action): RedirectResponse
    {
        $this->authorize('create', Sku::class);
        $action->execute($product->id, CreateBulkSkuDTO::fromRequest($request));

        return redirect()->route('admin.products.index')
            ->with('success', 'Variantes inyectadas al catálogo.');
    }

    public function edit(Sku $sku): InertiaResponse
    {
        $this->authorize('update', $sku);
        $sku->load('product');
    
        return Inertia::render('Admin/Skus/Edit', [
            'sku' => $sku,
            'product' => $sku->product
        ]);
    }

    public function update(UpdateSkuRequest $request, Sku $sku, UpdateSkuAction $action): RedirectResponse
    {
        $this->authorize('update', $sku);
        $action->execute($sku, SkuDataDTO::fromRequest($request)); // Usamos el DTO estricto
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Parámetros de variante actualizados.');
    }

    public function destroy(Sku $sku, DeleteSkuAction $action): RedirectResponse
    {
        $this->authorize('delete', $sku);
        $action->execute($sku);

        return back()->with('warning', 'Variante extraída de circulación.');
    }
}