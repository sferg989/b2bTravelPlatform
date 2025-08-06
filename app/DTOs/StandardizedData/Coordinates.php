<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized coordinates data transfer object
 */
readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            latitude: (float) ($data['latitude'] ?? 0.0),
            longitude: (float) ($data['longitude'] ?? 0.0),
        );
    }
}