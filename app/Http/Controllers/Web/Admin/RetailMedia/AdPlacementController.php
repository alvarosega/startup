<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\RetailMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RetailMedia\StorePlacementRequest;
use App\DTOs\Admin\RetailMedia\PlacementData;
use App\Actions\Admin\RetailMedia\{StorePlacementAction, UpdatePlacementAction};
use App\Models\AdPlacement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdPlacementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/RetailMedia/Placements/Index', [
            'placements' => AdPlacement::orderBy('code', 'asc')->get()
        ]);
    }

    public function store(StorePlacementRequest $request, StorePlacementAction $action): RedirectResponse
    {
        $action->execute(PlacementData::fromRequest($request->validated()));
        return back()->with('toast', ['type' => 'success', 'message' => 'Espacio comercial indexado con éxito.']);
    }

    public function update(StorePlacementRequest $request, string $id, UpdatePlacementAction $action): RedirectResponse
    {
        $placement = AdPlacement::findOrFail($id);
        $action->execute($placement, PlacementData::fromRequest($request->validated()));
        return back()->with('toast', ['type' => 'success', 'message' => 'Configuración de espacio publicitario actualizada.']);
    }

    public function destroy(string $id): RedirectResponse
    {
        $placement = AdPlacement::findOrFail($id);
        if ($placement->creatives()->exists()) {
            return back()->with('toast', ['type' => 'error', 'message' => 'No es posible eliminar un espacio publicitario que contenga piezas creativas vinculadas.']);
        }
        $placement->delete();
        return back()->with('toast', ['type' => 'info', 'message' => 'Espacio comercial removido del nodo.']);
    }
}