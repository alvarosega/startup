<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\DTOs\Admin\RetailMedia\CreativeData;
use App\Models\AdCreative;
use Illuminate\Support\Facades\DB;

final class StoreCreativeAction
{
    public function execute(CreativeData $data): AdCreative
    {
        return DB::transaction(function () use ($data) {
            $pathMobile  = $data->imageMobile ? $data->imageMobile->store('creatives/mobile', 'public') : null;
            $pathDesktop = $data->imageDesktop ? $data->imageDesktop->store('creatives/desktop', 'public') : null;

            return AdCreative::create([
                'campaign_id'        => $data->campaignId,
                'placement_id'       => $data->placementId,
                'branch_id'          => $data->branchId,
                'sku_id'             => $data->skuId,
                'category_id'        => $data->categoryId,
                'bundle_id'          => $data->bundleId,
                'brand_id'           => $data->brandId,
                'target_id'          => $data->targetId,
                'target_type'        => $data->targetType,
                'name'               => $data->name,
                'image_mobile_path'  => $pathMobile,
                'image_desktop_path' => $pathDesktop,
                'action_type'        => $data->actionType,
                'sort_order'         => $data->sortOrder,
                'is_active'          => $data->isActive,
            ]);
        });
    }
}