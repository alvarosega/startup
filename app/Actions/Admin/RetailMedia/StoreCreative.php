<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\Models\RetailMedia\AdCreative;
use App\DTOs\Admin\RetailMedia\CreativeData;

final class StoreCreative
{
    public function execute(CreativeData $data): AdCreative
    {
        $pathMobile = $data->image_mobile ? $data->image_mobile->store('creatives', 'public') : null;
        $pathDesktop = $data->image_desktop ? $data->image_desktop->store('creatives', 'public') : null;

        return AdCreative::create([
            'campaign_id'        => $data->campaign_id,
            'placement_id'       => $data->placement_id,
            'branch_id'          => $data->branch_id,
            'sku_id'             => $data->sku_id,
            'category_id'        => $data->category_id,
            'brand_id'           => $data->brand_id,
            'bundle_id'          => $data->bundle_id,
            'name'               => $data->name,
            'image_mobile_path'  => $pathMobile,
            'image_desktop_path' => $pathDesktop,
            'action_type'        => $data->action_type,
            'sort_order'         => $data->sort_order,
            'is_active'          => $data->is_active
        ]);
    }
}