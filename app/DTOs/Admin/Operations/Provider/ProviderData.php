<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Operations\Provider;

use Illuminate\Http\Request;

readonly class ProviderData
{
    public function __construct(
        public ?string $id,
        public string $company_name,
        public ?string $commercial_name,
        public string $tax_id,
        public ?string $internal_code,
        public ?string $contact_name,
        public ?string $email_orders,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public int $lead_time_days,
        public float $min_order_value,
        public int $credit_days,
        public float $credit_limit,
        public bool $is_active,
        public ?string $notes,
        public int $version
    ) {}

    public static function fromRequest(Request $request, ?string $id = null): self
    {
        $validated = $request->validated();
        $email = $validated['email_orders'] ?? null;
        $phone = $validated['phone'] ?? null;

        return new self(
            id: $id,
            company_name: trim((string) $validated['company_name']),
            commercial_name: $validated['commercial_name'] ?? null,
            tax_id: (string) $validated['tax_id'],
            internal_code: $validated['internal_code'] ?? null,
            contact_name: $validated['contact_name'] ?? null,
            email_orders: $email ? strtolower(trim((string) $email)) : null,
            phone: $phone ? preg_replace('/[^\+0-9]/', '', (string) $phone) : null,
            address: $validated['address'] ?? null,
            city: $validated['city'] ?? null,
            lead_time_days: (int) ($validated['lead_time_days'] ?? 1),
            min_order_value: (float) ($validated['min_order_value'] ?? 0),
            credit_days: (int) ($validated['credit_days'] ?? 0),
            credit_limit: (float) ($validated['credit_limit'] ?? 0),
            is_active: $request->boolean('is_active', true),
            notes: $validated['notes'] ?? null,
            version: (int) ($validated['version'] ?? 0)
        );
    }
}