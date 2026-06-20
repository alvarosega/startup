<?php

namespace App\Http\Resources\Customer\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => (string) $this->id,
            'rating'  => (int) $this->rating,
            'comment' => $this->comment ? (string) $this->comment : null,
            'date'    => $this->created_at->diffForHumans(),
            'customer_name' => $this->customer->profile->first_name ?? 'Cliente',
        ];
    }
}