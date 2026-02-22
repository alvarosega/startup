<?php

namespace App\DTOs\Customer\Profiles;

use Illuminate\Http\Request;

readonly class ProfileUpdateData
{
    public function __construct(
        public string $email,
        public string $firstName,
        public string $lastName,
        public ?string $birthDate,
        public ?string $gender
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email:     $request->validated('email'),
            firstName: $request->validated('first_name'),
            lastName:  $request->validated('last_name'),
            birthDate: $request->validated('birth_date'),
            gender:    $request->validated('gender'),
        );
    }
}