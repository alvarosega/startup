<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class RegisterCustomerData
{
    public function __construct(
        public string $phone,
        public string $email,
        public string $password,
        public string $address,
        public ?string $alias,
        public ?string $details,
        public float $latitude,
        public float $longitude,
        public ?string $branch_id, // Hexadecimal String
        public string $avatar_type,
        public ?string $avatar_source,
        public ?UploadedFile $avatar_file,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            address: $request->validated('address'),
            alias: $request->validated('alias') ?? 'Casa',
            details: $request->validated('details'),
            latitude: (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            branch_id: $request->validated('branch_id'),
            avatar_type: $request->validated('avatar_type'),
            avatar_source: $request->validated('avatar_source'),
            avatar_file: $request->file('avatar_file'),
        );
    }
}