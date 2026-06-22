<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bundle;

use App\Models\Bundle\Bundle;
use App\Models\Bundle\BundleItem;
use App\DTOs\Admin\Bundle\BundleData;
use Illuminate\Support\Facades\DB;

final class StoreBundle
{
    public function execute(BundleData $data): Bundle
    {
        return DB::transaction(function () use ($data) {
            $imagePath = $data->image ? $data->image->store('bundles', 'public') : null;

            $bundle = Bundle::create([
                'name'       => $data->name,
                'image_path' => $imagePath,
                'type'       => $data->type,
                'starts_at'  => $data->starts_at,
                'ends_at'    => $data->ends_at,
                'is_active'  => $data->is_active
            ]);

            foreach ($data->items as $item) {
                BundleItem::create([
                    'bundle_id' => $bundle->id,
                    'sku_id'    => $item->sku_id,
                    'quantity'  => $item->quantity
                ]);
            }

            return $bundle;
        });
    }
}