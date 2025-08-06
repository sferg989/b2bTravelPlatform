<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized availability data transfer object
 */
readonly class Availability
{
    public function __construct(
        public bool $isAvailable,
        public Price $price,
    ) {}

    public function toArray(): array
    {
        return [
            'is_available' => $this->isAvailable,
            'price' => $this->price->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isAvailable: $data['is_available'] ?? false,
            price: Price::fromArray($data['price'] ?? []),
        );
    }
}