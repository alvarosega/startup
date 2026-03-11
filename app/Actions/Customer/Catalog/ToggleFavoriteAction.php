<?php

namespace App\Actions\Customer\Catalog;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\DTOs\Customer\Catalog\ToggleFavoriteDTO;

class ToggleFavoriteAction
{
    public function execute(ToggleFavoriteDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $customer = Customer::findOrFail($dto->customerId);
            
            // Toggle atómico: adjunta o separa el registro en la tabla pivote
            $result = $customer->favoriteProducts()->toggle($dto->productId);

            return [
                'is_favorited' => count($result['attached']) > 0
            ];
        });
    }
}