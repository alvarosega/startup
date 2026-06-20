<?php
namespace App\DTOs\Admin\Provider;

use Illuminate\Http\Request;


readonly class ProviderData {
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
        public int $version = 0,
    ) {
        // CONSTRUCTOR PURO: Sin lógica de asignación para evitar colisión con readonly.
    }

    public static function fromRequest(Request $request, ?string $id = null): self {
        // REGLA 2.B: Normalización preventiva antes de la instanciación
        $phone = $request->validated('phone');
        $email = $request->validated('email_orders');

        return new self(
            id: $id,
            company_name: trim((string) $request->validated('company_name')),
            commercial_name: $request->validated('commercial_name'),
            tax_id: (string) $request->validated('tax_id'),
            internal_code: $request->validated('internal_code'),
            contact_name: $request->validated('contact_name'),
            email_orders: $email ? strtolower(trim($email)) : null,
            phone: $phone ? preg_replace('/[^\+0-9]/', '', $phone) : null,
            address: $request->validated('address'),
            city: $request->validated('city'),
            lead_time_days: (int) $request->validated('lead_time_days', 1),
            min_order_value: (float) $request->validated('min_order_value', 0),
            credit_days: (int) $request->validated('credit_days', 0),
            credit_limit: (float) $request->validated('credit_limit', 0),
            is_active: $request->boolean('is_active', true),
            notes: $request->validated('notes'),
            version: (int) $request->validated('version', 0),
        );
    }
}