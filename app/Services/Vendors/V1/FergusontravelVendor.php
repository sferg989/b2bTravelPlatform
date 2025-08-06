<?php

declare(strict_types=1);

namespace App\Services\Vendors\V1;

use App\DTOs\StandardizedData\Address;
use App\DTOs\StandardizedData\Amenity;
use App\DTOs\StandardizedData\Availability;
use App\DTOs\StandardizedData\Coordinates;
use App\DTOs\StandardizedData\Price;
use App\DTOs\StandardizedData\StandardizedFlight;
use App\DTOs\StandardizedData\StandardizedHotel;
use App\DTOs\Vendor\SearchCriteria;
use App\DTOs\Vendor\VendorResponse;
use App\Services\Vendors\Contracts\VendorInterface;
use Illuminate\Http\Client\Factory as HttpClient;

/**
 * Fergusontravel vendor implementation
 * Handles JSON/ API communication and data transformation
 */
class FergusontravelVendor implements VendorInterface
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $baseUrl,
    ) {}

    public function getVendorCode(): string
    {
        return 'FERGUSONTRAVEL';
    }

    public function getVendorName(): string
    {
        return 'Fergusontravel';
    }

    public function isAvailable(): bool
    {
        return !empty($this->clientId) && !empty($this->baseUrl);
    }

    public function searchHotels(SearchCriteria $criteria): VendorResponse
    {
        try {
            // Mock JSON response for demonstration
            $mockResponse = [
                'data' => [
                    'hotelSearch' => [
                        'hotels' => [
                            [
                                'id' => 'HTL_FRG_001',
                                'name' => 'Ferguson Grand Hotel',
                                'description' => 'Luxury hotel in downtown',
                                'address' => [
                                    'street' => '123 Business Ave',
                                    'city' => $criteria->destination,
                                    'country' => 'United States',
                                    'coordinates' => [
                                        'latitude' => 40.7589,
                                        'longitude' => -73.9851,
                                    ],
                                ],
                                'starRating' => 4,
                                'amenities' => ['WiFi', 'Pool', 'Gym'],
                                'availability' => [
                                    'price' => [
                                        'amount' => 299.99,
                                        'currency' => 'USD',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $transformedData = $this->transformHotelSearchResponse($mockResponse);

            return VendorResponse::success(
                data: $transformedData,
                resultCount: count($transformedData),
                vendorTransactionId: 'FRG_' . uniqid()
            );

        } catch (\Exception $e) {
            return VendorResponse::error(
                "Fergusontravel error: {$e->getMessage()}",
                'VENDOR_ERROR'
            );
        }
    }

    public function searchFlights(SearchCriteria $criteria): VendorResponse
    {
        try {
            // Mock JSON response for demonstration
            $mockResponse = [
                'data' => [
                    'flightSearch' => [
                        'flights' => [
                            [
                                'id' => 'FLT_FRG_001',
                                'airline' => 'FA',
                                'flightNumber' => 'FA123',
                                'departure' => [
                                    'airport' => $criteria->origin,
                                    'dateTime' => $criteria->departureDate?->toISOString(),
                                ],
                                'arrival' => [
                                    'airport' => $criteria->destination,
                                    'dateTime' => $criteria->departureDate?->addHours(3)->toISOString(),
                                ],
                                'pricing' => [
                                    'amount' => 499.99,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $transformedData = $this->transformFlightSearchResponse($mockResponse);

            return VendorResponse::success(
                data: $transformedData,
                resultCount: count($transformedData),
                vendorTransactionId: 'FRG_' . uniqid()
            );

        } catch (\Exception $e) {
            return VendorResponse::error(
                "Fergusontravel error: {$e->getMessage()}",
                'VENDOR_ERROR'
            );
        }
    }

    private function transformHotelSearchResponse(mixed $vendorData): array
    {
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

    private function transformFlightSearchResponse(mixed $vendorData): array
    {
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
}
