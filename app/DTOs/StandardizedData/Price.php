<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized price data transfer object
 */
readonly class Price
{
    public function __construct(
        public float $amount,
        public string $currency = 'USD',
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? 'USD',
        );
    }
}