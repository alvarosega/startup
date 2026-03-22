<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use App\DTOs\Admin\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertBrandAction
{
    public function execute(BrandData $data, ?Brand $brand = null): Brand
    {
        return DB::transaction(function () use ($data, $brand) {
            $isNew = !$brand;
            $oldPath = $brand?->image_path;

            $attributes = [
                'parent_id'   => $data->parent_id,
                'provider_id' => $data->provider_id,
                'category_id' => $data->category_id,
                'name'        => $data->name,
                'slug'        => $data->slug,
                'website'     => $data->website,
                'description' => $data->description,
                'is_active'   => $data->is_active,
                'is_featured' => $data->is_featured,
                'sort_order'  => $data->sort_order,
            ];

            if ($data->image) {
                $attributes['image_path'] = $data->image->store('brands', 'public');
            }

            if ($isNew) {
                $brand = Brand::create($attributes);
            } else {
                $brand->update($attributes);
                if ($data->image && $oldPath) Storage::disk('public')->delete($oldPath);
            }

            // Sincronización de Tabla Pivot (Market Zones)
            $brand->marketZones()->sync($data->market_zone_ids);
            
            Cache::forget('admin_brands_list');
            
            return $brand;
        });
    }
}