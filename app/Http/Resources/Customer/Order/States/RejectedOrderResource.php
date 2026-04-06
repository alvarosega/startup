<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Order\States;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RejectedOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}