<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Category;

final readonly class CategoryDetailsData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public bool $requiresAgeCheck,
        public ?string $bgColor
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            requiresAgeCheck: (bool) ($data['requires_age_check'] ?? false),
            bgColor: isset($data['bg_color']) ? (string) $data['bg_color'] : null
        );
    }

    /**
     * @return array{id: string, name: string, slug: string, requires_age_check: bool, bg_color: ?string}
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'requires_age_check' => $this->requiresAgeCheck,
            'bg_color'           => $this->bgColor,
        ];
    }
}