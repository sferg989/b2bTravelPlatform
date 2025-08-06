<?php

declare(strict_types=1);

namespace App\Services\DataManagement\Transformers;

use App\Services\DataManagement\Contracts\DataTransformerInterface;
use App\DTOs\StandardizedData\StandardizedHotel;
use App\DTOs\StandardizedData\StandardizedFlight;
use App\DTOs\StandardizedData\Address;
use App\DTOs\StandardizedData\Coordinates;
use App\DTOs\StandardizedData\Amenity;
use App\DTOs\StandardizedData\Availability;
use App\DTOs\StandardizedData\Price;
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

        return array_map(fn(array $hotel): StandardizedHotel => 
            new StandardizedHotel(
                externalId: $hotel['id'] ?? '',
                vendorCode: $this->getVendorCode(),
                name: $hotel['name'] ?? '',
                description: $hotel['description'] ?? '',
                address: new Address(
                    street: $hotel['address']['street'] ?? '',
                    city: $hotel['address']['city'] ?? '',
                    country: $hotel['address']['country'] ?? '',
                ),
                coordinates: new Coordinates(
                    latitude: (float) ($hotel['address']['coordinates']['latitude'] ?? 0),
                    longitude: (float) ($hotel['address']['coordinates']['longitude'] ?? 0),
                ),
                starRating: (int) ($hotel['starRating'] ?? 0),
                amenities: array_map(fn(string $amenity): Amenity => 
                    new Amenity(
                        name: $amenity,
                        category: 'general',
                    ), 
                    $hotel['amenities'] ?? []
                ),
                availability: new Availability(
                    isAvailable: !empty($hotel['availability']),
                    price: new Price(
                        amount: (float) ($hotel['availability']['price']['amount'] ?? 0),
                        currency: $hotel['availability']['price']['currency'] ?? 'USD',
                    ),
                ),
            ), 
            $hotels
        );
    }

    public function transformFlightSearchResponse(mixed $vendorData): array
    {
        if (!$this->canTransform($vendorData)) {
            throw new InvalidArgumentException('Invalid JSON data provided');
        }

        $flights = $vendorData['data']['flightSearch']['flights'] ?? [];

        return array_map(fn(array $flight): StandardizedFlight => 
            new StandardizedFlight(
                externalId: $flight['id'] ?? '',
                vendorCode: $this->getVendorCode(),
                airlineCode: $flight['airline'] ?? '',
                flightNumber: $flight['flightNumber'] ?? '',
                departureAirport: $flight['departure']['airport'] ?? '',
                arrivalAirport: $flight['arrival']['airport'] ?? '',
                departureDateTime: $flight['departure']['dateTime'] ?? '',
                arrivalDateTime: $flight['arrival']['dateTime'] ?? '',
                availability: new Availability(
                    isAvailable: !empty($flight['pricing']),
                    price: new Price(
                        amount: (float) ($flight['pricing']['amount'] ?? 0),
                        currency: $flight['pricing']['currency'] ?? 'USD',
                    ),
                ),
            ), 
            $flights
        );
    }

    public function transformHotelDetails(mixed $vendorData): StandardizedHotel
    {
        $hotels = $this->transformHotelSearchResponse(['data' => ['hotelSearch' => ['hotels' => [$vendorData]]]]);
        if (empty($hotels)) {
            throw new InvalidArgumentException('No hotel data found in vendor response');
        }
        return $hotels[0];
    }

    public function transformFlightDetails(mixed $vendorData): StandardizedFlight
    {
        $flights = $this->transformFlightSearchResponse(['data' => ['flightSearch' => ['flights' => [$vendorData]]]]);
        if (empty($flights)) {
            throw new InvalidArgumentException('No flight data found in vendor response');
        }
        return $flights[0];
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