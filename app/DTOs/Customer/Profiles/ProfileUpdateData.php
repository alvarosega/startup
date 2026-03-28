<?php

namespace App\DTOs\Customer\Profiles;

use App\Http\Requests\Customer\Profiles\UpdateProfileRequest;

readonly class ProfileUpdateData
{
    public function __construct(
        public ?string $birthDate,
        public ?string $gender,
    ) {}

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        $v = $request->validated();

        return new self(
            birthDate: $v['birth_date'] ?? null,
            gender:    $v['gender'] ?? null,
        );
    }
}