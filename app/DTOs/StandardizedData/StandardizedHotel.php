<?php

declare(strict_types=1);

namespace App\DTOs\StandardizedData;

/**
 * Standardized hotel data transfer object
 */
readonly class StandardizedHotel
{
    /**
     * @param Amenity[] $amenities
     */
    public function __construct(
        public string $externalId,
        public string $vendorCode,
        public string $name,
        public string $description,
        public Address $address,
        public Coordinates $coordinates,
        public int $starRating,
        public array $amenities,
        public Availability $availability,
    ) {}

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'vendor_code' => $this->vendorCode,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address->toArray(),
            'coordinates' => $this->coordinates->toArray(),
            'star_rating' => $this->starRating,
            'amenities' => array_map(fn(Amenity $amenity) => $amenity->toArray(), $this->amenities),
            'availability' => $this->availability->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $amenities = array_map(
            fn(array $amenityData) => Amenity::fromArray($amenityData),
            $data['amenities'] ?? []
        );

        return new self(
            externalId: $data['external_id'] ?? '',
            vendorCode: $data['vendor_code'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            address: Address::fromArray($data['address'] ?? []),
            coordinates: Coordinates::fromArray($data['coordinates'] ?? []),
            starRating: (int) ($data['star_rating'] ?? 0),
            amenities: $amenities,
            availability: Availability::fromArray($data['availability'] ?? []),
        );
    }
}