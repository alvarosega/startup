<?php

declare(strict_types=1);

namespace App\Services\Customer\Queries;

use App\Models\Operations\Branch;
use App\Exceptions\Customer\Auth\MissingDefaultBranchException;

final class BranchQueryService
{
    public function activeBranchExists(string $id): bool
    {
        return Branch::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->exists();
    }

    public function getDefaultBranchId(): string
    {
        /** @var string|null $branchId */
        $branchId = Branch::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->where('deleted_epoch', 0)
            ->value('id');

        if (!$branchId) {
            throw new MissingDefaultBranchException();
        }

        return $branchId;
    }

    public function getActiveBranches(): array
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->select([
                'id', 'name', 'slug', 'city',
                \Illuminate\Support\Facades\DB::raw('ST_AsGeoJSON(coverage_polygon) as polygon_geojson')
            ])
            ->get();

        return $branches->map(function ($branch) {
            $polygon = [];
            if ($branch->polygon_geojson) {
                $geo = json_decode((string) $branch->polygon_geojson, true);
                $polygon = $geo['coordinates'][0] ?? [];
            }

            return [
                'id'               => (string) $branch->id,
                'name'             => (string) $branch->name,
                'slug'             => (string) $branch->slug,
                'city'             => (string) $branch->city,
                'coverage_polygon' => $polygon,
            ];
        })->all();
    }

    public function getCollidingBranches(float $lat, float $lng): array
    {
        return Branch::query()
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->whereRaw(
                "ST_Contains(coverage_polygon, ST_GeomFromText(?, 4326))",
                ["POINT({$lng} {$lat})"]
            )
            ->select('id', 'name', 'slug', 'city')
            ->get()
            ->toArray();
    }
    public function getBranchNameById(string $id): ?string
    {
        return Branch::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->value('name');
    }
    public function getBranchContextData(string $id): array
    {
        $data = Branch::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->select(['id', 'name', 'is_default'])
            ->first();

        if ($data) {
            return [
                'id'         => (string) $data->id,
                'name'       => (string) $data->name,
                'is_default' => (bool) $data->is_default,
            ];
        }

        // Fallback seguro: Obtener la sucursal predeterminada real de la base de datos
        $default = Branch::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->where('deleted_epoch', 0)
            ->select(['id', 'name', 'is_default'])
            ->first() ?? Branch::query()->where('deleted_epoch', 0)->first();

        return [
            'id'         => (string) ($default?->id ?? ''),
            'name'       => (string) ($default?->name ?? 'Sucursal Central'),
            'is_default' => true,
        ];
    }
}