<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users\Driver;

use App\Http\Requests\Admin\Users\Driver\ChangeDriverStatusRequest;

final class ChangeDriverStatusDTO
{
    public function __construct(
        public readonly string $driverId,
        public readonly string $status,
        public readonly ?string $rejectionReason
    ) {}

    public static function fromRequest(ChangeDriverStatusRequest $request, string $driverId): self
    {
        return new self(
            driverId: $driverId,
            status: $request->validated('status'),
            rejectionReason: $request->validated('rejection_reason')
        );
    }
}