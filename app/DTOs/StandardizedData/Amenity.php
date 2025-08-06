<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized amenity data transfer object
 */
readonly class Amenity
{
    public function __construct(
        public string $name,
        public string $category = 'general',
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            category: $data['category'] ?? 'general',
        );
    }
}