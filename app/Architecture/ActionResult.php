<?php

declare(strict_types=1);

namespace App\Architecture;

final readonly class ActionResult
{
    private function __construct(
        public bool $isSuccess,
        public mixed $data = null,
        public ?string $errorMessage = null
    ) {}

    public static function success(mixed $data = null): static
    {
        return new static(true, $data, null);
    }

    public static function failure(string $message): static
    {
        return new static(false, null, $message);
    }
}