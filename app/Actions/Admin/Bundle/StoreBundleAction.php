<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bundle;

use App\DTOs\Admin\Bundle\BundleData;
use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class StoreBundleAction
{
    public function execute(BundleData $data): Bundle
    {
        return DB::transaction(function () use ($data) {
            $imagePath = null;

            if ($data->image) {
                $imagePath = $data->image->store('bundles', 'public');
            }

            $bundle = Bundle::create([
                'name'       => $data->name,
                'type'       => $data->type,
                'is_active'  => $data->isActive,
                'image_path' => $imagePath,
            ]);

            $bundle->skus()->attach($data->skuIds);

            return $bundle;
        });
    }
}