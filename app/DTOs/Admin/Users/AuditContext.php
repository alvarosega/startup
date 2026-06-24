<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users;

use Illuminate\Http\Request;

final class AuditContext
{
    public function __construct(
        public readonly string $causerType,
        public readonly string $causerId,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            causerType: 'App\Models\Admin', // Fijo debido al middleware de SuperAdmin
            causerId: (string) $request->user()->id,
            ipAddress: $request->ip(),
            userAgent: $request->header('User-Agent')
        );
    }
}