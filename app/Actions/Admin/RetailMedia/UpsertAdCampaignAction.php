<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCampaign;
use App\DTOs\Admin\RetailMedia\AdCampaignDTO;
use Illuminate\Support\Facades\DB;

class UpsertAdCampaignAction
{
    public function execute(AdCampaignDTO $dto): AdCampaign
    {
        return DB::transaction(fn() => AdCampaign::updateOrCreate(
            ['id' => $dto->id],
            [
                'provider_id' => $dto->provider_id,
                'market_zone_id' => $dto->market_zone_id,
                'name' => $dto->name,
                'type' => $dto->type,
                'starts_at' => $dto->starts_at,
                'ends_at' => $dto->ends_at,
                'is_active' => $dto->is_active,
            ]
        ));
    }
}