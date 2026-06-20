<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bundle;

use App\DTOs\Admin\Bundle\BundleData;
use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateBundleAction
{
    public function execute(Bundle $bundle, BundleData $data): Bundle
    {
        return DB::transaction(function () use ($bundle, $data) {
            $imagePath = $bundle->image_path;

            if ($data->image) {
                if ($bundle->image_path) {
                    Storage::disk('public')->delete($bundle->image_path);
                }
                $imagePath = $data->image->store('bundles', 'public');
            }

            $bundle->update([
                'name'       => $data->name,
                'type'       => $data->type,
                'is_active'  => $data->isActive,
                'image_path' => $imagePath,
            ]);

            $bundle->skus()->sync($data->skuIds);

            return $bundle;
        });
    }
}