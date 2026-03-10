<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use App\DTOs\Admin\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage, Cache};
use Illuminate\Http\UploadedFile;

class UpsertBrandAction
{
    public function execute(BrandData $data, ?Brand $brand = null): Brand
    {
        return DB::transaction(function () use ($data, $brand) {
            $isNew = !$brand;
            $oldPath = $brand?->image_path;

            $attributes = [
                'provider_id'    => $data->provider_id,
                'category_id'    => $data->category_id,
                'market_zone_id' => $data->market_zone_id,
                'name'           => $data->name,
                'slug'           => $data->slug,
                'website'        => $data->website,
                'description'    => $data->description,
                'is_active'      => $data->is_active,
                'is_featured'    => $data->is_featured,
                'sort_order'     => $data->sort_order,
            ];

            if ($data->image instanceof UploadedFile) {
                $attributes['image_path'] = $data->image->store('brands', 'public');
            }

            if ($isNew) {
                $brand = Brand::create($attributes);
            } else {
                $brand->update($attributes);
                if ($data->image instanceof UploadedFile && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            // LA LEY 2.0: Invalidación infalible usando Timestamp en lugar de increment
            Cache::put('admin_brands_version', now()->timestamp);
            
            return $brand;
        });
    }
}