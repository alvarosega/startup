<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\DTOs\Admin\RetailMedia\PlacementData;
use App\Models\AdPlacement;

final class StorePlacementAction
{
    public function execute(PlacementData $data): AdPlacement
    {
        return AdPlacement::create([
            'name'      => $data->name,
            'code'      => $data->code,
            'max_items' => $data->maxItems,
            'is_active' => $data->isActive,
        ]);
    }
}

final class UpdatePlacementAction
{
    public function execute(AdPlacement $placement, PlacementData $data): AdPlacement
    {
        $placement->update([
            'name'      => $data->name,
            'code'      => $data->code,
            'max_items' => $data->maxItems,
            'is_active' => $data->isActive,
        ]);
        return $placement;
    }
}