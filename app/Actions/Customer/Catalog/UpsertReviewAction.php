<?php

namespace App\Actions\Customer\Catalog;

use App\Models\Review;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\DTOs\Customer\Catalog\UpsertReviewDTO;
use Illuminate\Validation\ValidationException;

class UpsertReviewAction
{
    public function execute(UpsertReviewDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // El updateOrCreate manejará la generación del UUID automáticamente por el trait HasUuids
            Review::updateOrCreate(
                [
                    'customer_id' => $dto->customerId,
                    'product_id'  => $dto->productId,
                ],
                [
                    'rating'  => $dto->rating,
                    'comment' => $dto->comment,
                ]
            );
        });
    }
}