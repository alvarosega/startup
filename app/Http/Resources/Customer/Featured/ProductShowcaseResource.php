<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Featured;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductShowcaseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'name' => $this->name,
                'description' => $this->description,
                'image_url' => $this->image_url,
            ],
            'skus' => $this->skus, // Ya vienen mapeados como DTOs/Arrays desde la Action
            'others_paginated' => [
                'data' => $this->others,
                'next_cursor' => $this->next_cursor,
            ]
        ];
    }


}