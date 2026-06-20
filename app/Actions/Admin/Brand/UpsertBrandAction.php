<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use App\DTOs\Admin\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertBrandAction
{
    public function execute(BrandData $data, ?Brand $brand = null): Brand
    {
        return DB::transaction(function () use ($data, $brand) {
            $isNew = !$brand;
            $oldPath = $brand?->image_path;
            $attributes = $data->toArray();

            if ($data->image) {
                $attributes['image_path'] = $data->image->store('brands', 'public');
            }

            if ($isNew) {
                $maxSortOrder = Brand::where('parent_id', $attributes['parent_id'])
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 10 : 10;
                $brand = Brand::create($attributes);
            } else {
                $brand->update($attributes);
                if ($data->image && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            return $brand;
        });
    }
}