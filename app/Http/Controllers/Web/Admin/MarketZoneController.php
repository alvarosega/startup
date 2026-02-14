<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketZone;
use App\Models\Category;
use App\Http\Requests\Market\MarketZoneRequest;
use App\DTOs\MarketZoneDTO;
use App\Actions\Market\UpsertMarketZoneAction;
use Inertia\Inertia;

class MarketZoneController extends Controller
{
    public function index()
    {
        $zones = MarketZone::withCount('categories')
            ->latest()
            ->get();

        return Inertia::render('Admin/MarketZones/Index', [
            'zones' => $zones
        ]);
    }

    public function create()
    {
        // CORRECCIÓN: Solo traemos las categorías PRINCIPALES (Roots)
        // Las subcategorías heredarán la lógica de su padre o no se mapean en el nivel macro.
        $categories = Category::roots()
            ->orderBy('name')
            ->get(['id', 'name', 'market_zone_id']);

        return Inertia::render('Admin/MarketZones/Create', [
            'available_categories' => $categories
        ]);
    }

    public function store(MarketZoneRequest $request, UpsertMarketZoneAction $action)
    {
        $dto = MarketZoneDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.market-zones.index')
            ->with('success', 'Zona creada y categorías principales asignadas.');
    }

    public function edit(MarketZone $marketZone)
    {
        // 1. Cargar las categorías asignadas (Solo necesitamos ID para el array del form)
        $marketZone->load('categories:id,name,market_zone_id');

        // 2. CORRECCIÓN: Solo categorías PRINCIPALES para el selector
        $categories = Category::roots()
            ->orderBy('name')
            ->get(['id', 'name', 'market_zone_id']);

        return Inertia::render('Admin/MarketZones/Edit', [
            'zone' => $marketZone,
            'available_categories' => $categories
        ]);
    }

    public function update(MarketZoneRequest $request, MarketZone $marketZone, UpsertMarketZoneAction $action)
    {
        $dto = MarketZoneDTO::fromRequest($request);
        $action->execute($dto, $marketZone);

        return redirect()->route('admin.market-zones.index')
            ->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(MarketZone $marketZone)
    {
        if ($marketZone->categories()->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar la zona porque tiene categorías asignadas.'
            ]);
        }

        $marketZone->delete();

        return redirect()->route('admin.market-zones.index')
            ->with('success', 'Zona eliminada.');
    }
}