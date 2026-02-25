<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use App\DTOs\Admin\Brand\BrandData; // Importación correcta
use Illuminate\Support\Facades\DB;

class CreateBrand
{
    public function execute(BrandData $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $imagePath = $data->image ? $data->image->store('brands', 'public') : null;

            return Brand::create([
                'name'         => $data->name,
                'slug'         => $data->slug,
                'provider_id'  => $data->provider_id,
                'category_id'  => $data->category_id,
                'website'      => $data->website,
                'description'  => $data->description,
                'is_active'    => $data->is_active,
                'is_featured'  => $data->is_featured,
                'sort_order'   => $data->sort_order,
                'image_path'   => $imagePath,
            ]);
        });
    }
}