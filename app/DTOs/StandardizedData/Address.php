<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized address data transfer object
 */
readonly class Address
{
    public function __construct(
        public string $street,
        public string $city,
        public string $country,
    ) {}

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? '',
            city: $data['city'] ?? '',
            country: $data['country'] ?? '',
        );
    }
}