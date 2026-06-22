<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\Models\RetailMedia\AdCreative;
use App\DTOs\Admin\RetailMedia\CreativeData;
use Illuminate\Support\Facades\Storage;

final class UpdateCreative
{
    public function execute(AdCreative $creative, CreativeData $data): void
    {
        if ($data->image_mobile) {
            if ($creative->image_mobile_path) {
                Storage::disk('public')->delete($creative->image_mobile_path);
            }
            $creative->image_mobile_path = $data->image_mobile->store('creatives', 'public');
        }

        if ($data->image_desktop) {
            if ($creative->image_desktop_path) {
                Storage::disk('public')->delete($creative->image_desktop_path);
            }
            $creative->image_desktop_path = $data->image_desktop->store('creatives', 'public');
        }

        $creative->update([
            'campaign_id'  => $data->campaign_id,
            'placement_id' => $data->placement_id,
            'branch_id'    => $data->branch_id,
            'sku_id'       => $data->sku_id,
            'category_id'  => $data->category_id,
            'brand_id'     => $data->brand_id,
            'bundle_id'    => $data->bundle_id,
            'name'         => $data->name,
            'action_type'  => $data->action_type,
            'sort_order'   => $data->sort_order,
            'is_active'    => $data->is_active
        ]);
    }
}