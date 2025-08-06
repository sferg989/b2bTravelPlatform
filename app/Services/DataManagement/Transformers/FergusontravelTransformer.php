<?php

declare(strict_types=1);

namespace App\Services\DataManagement\Transformers;

use App\Services\DataManagement\Contracts\DataTransformerInterface;
use InvalidArgumentException;

/**
 * Fergusontravel JSON format transformer
 * Transforms JSON API responses to standardized data format
 */
class FergusontravelTransformer implements DataTransformerInterface
{
    public function transformHotelSearchResponse(mixed $vendorData): array
    {
        if (!$this->canTransform($vendorData)) {
            throw new InvalidArgumentException('Invalid JSON data provided');
        }

        $hotels = $vendorData['data']['hotelSearch']['hotels'] ?? [];

        return array_map(fn(array $hotel): array => [
            'external_id' => $hotel['id'] ?? '',
            'vendor_code' => $this->getVendorCode(),
            'name' => $hotel['name'] ?? '',
            'description' => $hotel['description'] ?? '',
            'address' => [
                'street' => $hotel['address']['street'] ?? '',
                'city' => $hotel['address']['city'] ?? '',
                'country' => $hotel['address']['country'] ?? '',
            ],
            'coordinates' => [
                'latitude' => (float) ($hotel['address']['coordinates']['latitude'] ?? 0),
                'longitude' => (float) ($hotel['address']['coordinates']['longitude'] ?? 0),
            ],
            'star_rating' => (int) ($hotel['starRating'] ?? 0),
            'amenities' => array_map(fn(string $amenity): array => [
                'name' => $amenity,
                'category' => 'general',
            ], $hotel['amenities'] ?? []),
            'availability' => [
                'is_available' => !empty($hotel['availability']),
                'price' => [
                    'amount' => (float) ($hotel['availability']['price']['amount'] ?? 0),
                    'currency' => $hotel['availability']['price']['currency'] ?? 'USD',
                ],
            ],
        ], $hotels);
    }

    public function transformFlightSearchResponse(mixed $vendorData): array
    {
        if (!$this->canTransform($vendorData)) {
            throw new InvalidArgumentException('Invalid JSON data provided');
        }

        $flights = $vendorData['data']['flightSearch']['flights'] ?? [];

        return array_map(fn(array $flight): array => [
            'external_id' => $flight['id'] ?? '',
            'vendor_code' => $this->getVendorCode(),
            'airline_code' => $flight['airline'] ?? '',
            'flight_number' => $flight['flightNumber'] ?? '',
            'departure_airport' => $flight['departure']['airport'] ?? '',
            'arrival_airport' => $flight['arrival']['airport'] ?? '',
            'departure_datetime' => $flight['departure']['dateTime'] ?? '',
            'arrival_datetime' => $flight['arrival']['dateTime'] ?? '',
            'availability' => [
                'is_available' => !empty($flight['pricing']),
                'price' => [
                    'amount' => (float) ($flight['pricing']['amount'] ?? 0),
                    'currency' => $flight['pricing']['currency'] ?? 'USD',
                ],
            ],
        ], $flights);
    }

    public function transformHotelDetails(mixed $vendorData): array
    {
        return $this->transformHotelSearchResponse(['data' => ['hotelSearch' => ['hotels' => [$vendorData]]]])[0] ?? [];
    }

    public function transformFlightDetails(mixed $vendorData): array
    {
        return $this->transformFlightSearchResponse(['data' => ['flightSearch' => ['flights' => [$vendorData]]]])[0] ?? [];
    }

    public function getVendorCode(): string
    {
        return 'FERGUSONTRAVEL';
    }

    public function getSupportedFormat(): string
    {
        return 'json';
    }

    public function canTransform(mixed $vendorData): bool
    {
        return is_array($vendorData) && 
               (isset($vendorData['data']['hotelSearch']) || 
                isset($vendorData['data']['flightSearch']) || 
                isset($vendorData['id']));
    }
}