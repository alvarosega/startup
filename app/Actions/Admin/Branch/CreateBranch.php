<?php

namespace App\Actions\Admin\Branch;

use App\Models\Branch;
use App\DTOs\Admin\Branch\BranchData;
use Illuminate\Support\Facades\DB;

class CreateBranch
{
    public function execute(BranchData $data): Branch
    {
        return DB::transaction(function () use ($data) {
            if ($data->is_default) {
                Branch::query()->where('is_default', true)->update(['is_default' => false]);
            }
            return Branch::create([
                'name' => $data->name,
                'city' => $data->city,
                'phone' => $data->phone,
                'address' => $data->address,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'coverage_polygon' => $data->coverage_polygon,
                'opening_hours' => $data->opening_hours,
                'is_default'  => $data->is_default,
                'is_active'   => $data->is_active,
            ]);
        });
    }
}