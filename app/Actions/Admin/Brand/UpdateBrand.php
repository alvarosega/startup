<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use App\DTOs\Admin\Brand\BrandData;
use Illuminate\Support\Facades\{DB, Storage};

class UpdateBrand
{
    public function execute(Brand $brand, BrandData $data): Brand
    {
        return DB::transaction(function () use ($brand, $data) {
            $attributes = [
                'name'        => $data->name,
                'slug'        => $data->slug,
                'provider_id' => $data->provider_id,
                'category_id' => $data->category_id,
                'website'     => $data->website,
                'description' => $data->description,
                'is_active'   => $data->is_active,
                'is_featured' => $data->is_featured,
                'sort_order'  => $data->sort_order,
            ];

            if ($data->image) {
                // Protocolo de reemplazo de assets
                $oldPath = $brand->image_path;
                $attributes['image_path'] = $data->image->store('brands', 'public');
                
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $brand->update($attributes);

            return $brand->fresh();
        });
    }
}