<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized flight data transfer object
 */
readonly class StandardizedFlight
{
    public function __construct(
        public string $externalId,
        public string $vendorCode,
        public string $airlineCode,
        public string $flightNumber,
        public string $departureAirport,
        public string $arrivalAirport,
        public string $departureDateTime,
        public string $arrivalDateTime,
        public Availability $availability,
    ) {}

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'vendor_code' => $this->vendorCode,
            'airline_code' => $this->airlineCode,
            'flight_number' => $this->flightNumber,
            'departure_airport' => $this->departureAirport,
            'arrival_airport' => $this->arrivalAirport,
            'departure_datetime' => $this->departureDateTime,
            'arrival_datetime' => $this->arrivalDateTime,
            'availability' => $this->availability->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: $data['external_id'] ?? '',
            vendorCode: $data['vendor_code'] ?? '',
            airlineCode: $data['airline_code'] ?? '',
            flightNumber: $data['flight_number'] ?? '',
            departureAirport: $data['departure_airport'] ?? '',
            arrivalAirport: $data['arrival_airport'] ?? '',
            departureDateTime: $data['departure_datetime'] ?? '',
            arrivalDateTime: $data['arrival_datetime'] ?? '',
            availability: Availability::fromArray($data['availability'] ?? []),
        );
    }
}