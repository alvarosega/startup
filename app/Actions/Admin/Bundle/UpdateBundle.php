<?php

declare(strict_types=1);

namespace App\Actions\Admin\Bundle;

use App\Models\Bundle\Bundle;
use App\Models\Bundle\BundleItem;
use App\DTOs\Admin\Bundle\BundleData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateBundle
{
    public function execute(Bundle $bundle, BundleData $data): void
    {
        DB::transaction(function () use ($bundle, $data) {
            if ($data->image) {
                if ($bundle->image_path) {
                    Storage::disk('public')->delete($bundle->image_path);
                }
                $bundle->image_path = $data->image->store('bundles', 'public');
            }

            $bundle->update([
                'name'      => $data->name,
                'type'      => $data->type,
                'starts_at' => $data->starts_at,
                'ends_at'   => $data->ends_at,
                'is_active' => $data->is_active
            ]);

            // Sincronización atómica de componentes: purga y reinserción
            BundleItem::where('bundle_id', $bundle->id)->delete();

            foreach ($data->items as $item) {
                BundleItem::create([
                    'bundle_id' => $bundle->id,
                    'sku_id'    => $item->sku_id,
                    'quantity'  => $item->quantity
                ]);
            }
        });
    }
}