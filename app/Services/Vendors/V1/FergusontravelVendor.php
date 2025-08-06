<?php

declare(strict_types=1);

namespace App\Services\Vendors\V1;

use App\DTOs\Vendor\SearchCriteria;
use App\DTOs\Vendor\VendorResponse;
use App\Services\DataManagement\DataManager;
use App\Services\Vendors\Contracts\VendorInterface;
use Illuminate\Http\Client\Factory as HttpClient;

/**
 * Fergusontravel vendor implementation
 * Handles JSON/GraphQL API communication and data transformation
 */
class FergusontravelVendor implements VendorInterface
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly DataManager $dataManager,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $baseUrl,
        private readonly string $graphqlEndpoint,
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
        return !empty($this->clientId) && 
               !empty($this->baseUrl) &&
               $this->dataManager->hasTransformer($this->getVendorCode());
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

            $transformedData = $this->dataManager->transformHotelSearchResponse(
                $this->getVendorCode(),
                $mockResponse
            );

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

            $transformedData = $this->dataManager->transformFlightSearchResponse(
                $this->getVendorCode(),
                $mockResponse
            );

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
}