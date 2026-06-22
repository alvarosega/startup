<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use App\DTOs\Admin\Catalog\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertBrandAction
{
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

        DB::beginTransaction();
        try {
            if ($isNew) {
                $maxSortOrder = Brand::where('parent_id', $attributes['parent_id'])
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 10 : 10;
                $brand = Brand::create($attributes);
            } else {
                $brand->update($attributes);
            }

            DB::commit();

            if ($data->image && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return $brand;

        } catch (\Exception $e) {
            DB::rollBack();
            
            foreach ($pathsToClean as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}