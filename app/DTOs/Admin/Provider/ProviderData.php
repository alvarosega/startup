<?php
namespace App\DTOs\Admin\Provider;

use Illuminate\Http\Request;

readonly class ProviderData
{
    public function __construct(
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
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            company_name: $request->validated('company_name'),
            commercial_name: $request->validated('commercial_name'),
            tax_id: $request->validated('tax_id'),
            internal_code: $request->validated('internal_code'),
            contact_name: $request->validated('contact_name'),
            email_orders: $request->validated('email_orders'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            lead_time_days: (int) $request->validated('lead_time_days', 1),
            min_order_value: (float) $request->validated('min_order_value', 0),
            credit_days: (int) $request->validated('credit_days', 0),
            credit_limit: (float) $request->validated('credit_limit', 0),
            is_active: (bool) $request->validated('is_active', true),
            notes: $request->validated('notes'),
        );
    }
}