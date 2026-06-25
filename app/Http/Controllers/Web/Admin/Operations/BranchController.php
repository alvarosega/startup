<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operations\Branch;
use App\DTOs\Admin\Operations\Branch\BranchData;
use App\Http\Requests\Admin\Operations\Branch\StoreBranchRequest;
use App\Http\Requests\Admin\Operations\Branch\UpdateBranchRequest;
use App\Actions\Admin\Operations\Branch\CreateBranch;
use App\Actions\Admin\Operations\Branch\UpdateBranch;
use App\Actions\Admin\Operations\Branch\ListBranches;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index(ListBranches $action): Response
    {
        return Inertia::render('Admin/Operations/Branches/Index', [
            'branches' => $action->execute()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Operations/Branches/Create');
    }

    public function store(StoreBranchRequest $request, CreateBranch $action): RedirectResponse
    {
        $action->execute(BranchData::fromRequest($request));

        return redirect()->route('admin.operations.branches.index')
            ->with('success', 'Sucursal operativa materializada con éxito.');
    }

    public function edit(Branch $branch): Response
    {
        $spatialData = Branch::select([
            DB::raw('ST_AsText(location) as location_wkt'),
            DB::raw('ST_AsText(coverage_polygon) as polygon_wkt')
        ])->where('id', $branch->id)->first();

        $latitude = 0.0;
        $longitude = 0.0;
        if ($spatialData && preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/i', (string) $spatialData->location_wkt, $matches)) {
            $longitude = (float) $matches[1];
            $latitude = (float) $matches[2];
        }

        $coveragePolygon = [];
        if ($spatialData && $spatialData->polygon_wkt && preg_match('/POLYGON\(\((.+)\)\)/i', (string) $spatialData->polygon_wkt, $polyMatches)) {
            $cleanedPolygonString = str_replace(', ', ',', $polyMatches[1]);
            $coordPairs = explode(',', $cleanedPolygonString);
            foreach ($coordPairs as $pair) {
                $coords = explode(' ', trim($pair));
                if (count($coords) === 2) {
                    // Se mantiene simetría pura para Inertia mapeando [longitud, latitud]
                    $coveragePolygon[] = [(float) $coords[0], (float) $coords[1]];
                }
            }
        }

        $mappedBranch = array_merge($branch->toArray(), [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'coverage_polygon' => $coveragePolygon
        ]);

        return Inertia::render('Admin/Operations/Branches/Edit', [
            'branch' => $mappedBranch
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $action): RedirectResponse
    {
        $action->execute($branch, BranchData::fromRequest($request));

        return redirect()->route('admin.operations.branches.index')
            ->with('success', 'Parámetros logísticos de la sucursal actualizados.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        
        return redirect()->back()->with('success', 'Nodo de sucursal extraído de circulación (Soft Delete).');
    }
}