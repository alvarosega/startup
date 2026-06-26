<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use App\DTOs\Admin\Catalog\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertBrandAction
{
    /**
     * RECTIFICACIÓN: Migración integral a DB::transaction encapsulando eventos físicos post-commit.
     */
    public function execute(BrandData $data, ?Brand $brand = null): Brand
    {
        $isNew = !$brand;
        $pathsToClean = [];
        $attributes = $data->toArray();
        $oldPath = $brand?->image_path;

        if ($data->image) {
            $attributes['image_path'] = $data->image->store('brands', 'public');
            $pathsToClean[] = $attributes['image_path'];
        }

        return DB::transaction(function () use ($isNew, $attributes, $data, $pathsToClean, $brand, $oldPath) {
            if ($isNew) {
                $maxSortOrder = Brand::where('parent_id', $attributes['parent_id'])
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 1 : 1;
                $brand = Brand::create($attributes);
            } else {
                $brand->update($attributes);
            }

            // Remoción diferida del archivo huérfano del disco únicamente tras un Commit exitoso
            DB::afterCommit(function () use ($data, $oldPath) {
                if ($data->image && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            });

            return $brand;
        });
    }
}