<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users\Driver;

use App\Http\Requests\Admin\Users\Driver\ChangeDriverStatusRequest;

final class ChangeDriverStatusDTO
{
    public function __construct(
        public readonly string $driverId,
        public readonly string $status,
        public readonly ?string $rejectionReason,
        public readonly ?string $branchId = null
    ) {}

    public static function fromRequest(\App\Http\Requests\Admin\Users\Driver\ChangeDriverStatusRequest $request, string $driverId): self
    {
        $validated = $request->validated();

        return new self(
            driverId: $driverId,
            status: (string) $validated['status'],
            rejectionReason: isset($validated['rejection_reason']) ? (string) $validated['rejection_reason'] : null,
            branchId: !empty($validated['branch_id']) ? (string) $validated['branch_id'] : null
        );
    }
}