<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Operations\Provider;

use Illuminate\Foundation\Http\FormRequest;

readonly class ProviderData
{
    public function __construct(
        public ?string $id,
        public string $companyName,
        public ?string $commercialName,
        public string $taxId,
        public ?string $internalCode,
        public ?string $contactName,
        public ?string $emailOrders,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public int $leadTimeDays,
        public float $minOrderValue,
        public int $creditDays,
        public float $creditLimit,
        public bool $isActive,
        public ?string $notes
    ) {}

    /**
     * Factoría estática adaptada para asimilar peticiones HTTP validadas eliminando el rastro de versiones.
     */
    public static function fromRequest(FormRequest $request, ?string $id = null): self
    {
        $validated = $request->validated();
        $email = $validated['email_orders'] ?? null;
        $phone = $validated['phone'] ?? null;

        return new self(
            id: $id,
            companyName: trim((string) $validated['company_name']),
            commercialName: !empty($validated['commercial_name']) ? trim((string) $validated['commercial_name']) : null,
            taxId: trim((string) $validated['tax_id']),
            internalCode: !empty($validated['internal_code']) ? trim((string) $validated['internal_code']) : null,
            contactName: !empty($validated['contact_name']) ? trim((string) $validated['contact_name']) : null,
            emailOrders: $email ? strtolower(trim((string) $email)) : null,
            phone: $phone ? preg_replace('/[^\+0-9]/', '', (string) $phone) : null,
            address: !empty($validated['address']) ? trim((string) $validated['address']) : null,
            city: !empty($validated['city']) ? trim((string) $validated['city']) : null,
            leadTimeDays: (int) ($validated['lead_time_days'] ?? 1),
            minOrderValue: (float) ($validated['min_order_value'] ?? 0.00),
            creditDays: (int) ($validated['credit_days'] ?? 0),
            creditLimit: (float) ($validated['credit_limit'] ?? 0.00),
            isActive: (bool) $request->boolean('is_active', true),
            notes: !empty($validated['notes']) ? trim((string) $validated['notes']) : null
        );
    }
}