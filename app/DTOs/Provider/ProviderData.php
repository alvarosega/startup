<?php

namespace App\DTOs\Provider;

class ProviderData
{
    public function __construct(
        public readonly string $companyName,
        public readonly ?string $commercialName,
        public readonly string $taxId,
        public readonly ?string $internalCode,
        public readonly ?string $contactName,
        public readonly ?string $emailOrders,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly int $leadTimeDays,
        public readonly float $minOrderValue,
        public readonly int $creditDays,
        public readonly float $creditLimit,
        public readonly bool $isActive,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            companyName: $request->validated('company_name'),
            commercialName: $request->validated('commercial_name'),
            taxId: $request->validated('tax_id'),
            internalCode: $request->validated('internal_code'),
            contactName: $request->validated('contact_name'),
            emailOrders: $request->validated('email_orders'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            leadTimeDays: (int) $request->validated('lead_time_days', 1),
            minOrderValue: (float) $request->validated('min_order_value', 0),
            creditDays: (int) $request->validated('credit_days', 0),
            creditLimit: (float) $request->validated('credit_limit', 0),
            isActive: $request->boolean('is_active', true),
            notes: $request->validated('notes'),
        );
    }

    public function toArray(): array
    {
        // Convertimos camelCase a snake_case para Eloquent
        return [
            'company_name' => $this->companyName,
            'commercial_name' => $this->commercialName,
            'tax_id' => $this->taxId,
            'internal_code' => $this->internalCode,
            'contact_name' => $this->contactName,
            'email_orders' => $this->emailOrders,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'lead_time_days' => $this->leadTimeDays,
            'min_order_value' => $this->minOrderValue,
            'credit_days' => $this->creditDays,
            'credit_limit' => $this->creditLimit,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
        ];
    }
}