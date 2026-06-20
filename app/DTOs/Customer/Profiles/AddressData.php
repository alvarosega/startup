<?php

namespace App\DTOs\Customer\Profiles;

use Illuminate\Http\Request;

readonly class AddressData
{
    public function __construct(
        public string $alias,
        public string $address,
        public ?string $details,
        public float $latitude,
        public float $longitude,
        public ?string $branchId,
        public bool $isDefault = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            alias:     $request->validated('alias'),
            address:   $request->validated('address'),
            details:   $request->validated('details'),
            latitude:  (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            branchId:  $request->validated('branch_id'),
            isDefault: (bool) $request->validated('is_default', false)
        );
    }
}