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

    /**
     * @return array<int, array{id: string, name: string, slug: string, city: string}>
     */
    public function getActiveBranches(): array
    {
        return Branch::query()
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->select('id', 'name', 'slug', 'city')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, array{id: string, name: string, slug: string, city: string}>
     */
    public function getCollidingBranches(float $lat, float $lng): array
    {
        return Branch::query()
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->whereRaw(
                "ST_Contains(coverage_polygon, ST_GeomFromText(?))",
                ["POINT({$lng} {$lat})"]
            )
            ->select('id', 'name', 'slug', 'city')
            ->get()
            ->toArray();
    }
    public function getBranchNameById(string $id): ?string
    {
        return \App\Models\Operations\Branch::query()->where('id', $id)->value('name');
    }

    /**
     * @return array{id: string, name: string, is_default: bool}
     */
    public function getBranchContextData(string $id): array
    {
        /** @var array{id: string, name: string, is_default: bool}|null $data */
        $data = \App\Models\Operations\Branch::query()
            ->select(['id', 'name', 'is_default'])
            ->find($id)
            ?->toArray();

        return $data ?? ['id' => '', 'name' => 'Sucursal Central', 'is_default' => true];
    }
}