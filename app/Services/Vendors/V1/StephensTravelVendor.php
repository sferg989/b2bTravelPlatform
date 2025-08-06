<?php

declare(strict_types=1);

namespace App\Services\Vendors\V1;

use App\DTOs\Vendor\SearchCriteria;
use App\DTOs\Vendor\VendorResponse;
use App\Services\DataManagement\DataManager;
use App\Services\Vendors\Contracts\VendorInterface;
use Illuminate\Http\Client\Factory as HttpClient;

/**
 * StephensTravel vendor implementation
 * Handles XML API communication and data transformation
 */
class StephensTravelVendor implements VendorInterface
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly DataManager $dataManager,
        private readonly string $apiKey,
        private readonly string $sharedSecret,
        private readonly string $baseUrl,
    ) {}

    public function getVendorCode(): string
    {
        return 'STEPHENSTRAVEL';
    }

    public function getVendorName(): string
    {
        return 'StephensTravel';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && 
               !empty($this->baseUrl) &&
               $this->dataManager->hasTransformer($this->getVendorCode());
    }

    public function searchHotels(SearchCriteria $criteria): VendorResponse
    {
        try {
            // Mock XML response for demonstration
            $mockXmlResponse = '<?xml version="1.0"?>
            <SearchResponse>
                <Hotel id="HTL_ST_001">
                    <Name>Stephens Luxury Resort</Name>
                    <Description>Beautiful resort with ocean views</Description>
                    <Address>
                        <Street>456 Resort Blvd</Street>
                        <City>' . $criteria->destination . '</City>
                        <Country>United States</Country>
                    </Address>
                    <Coordinates>
                        <Latitude>25.7617</Latitude>
                        <Longitude>-80.1918</Longitude>
                    </Coordinates>
                    <StarRating>5</StarRating>
                    <Amenities>
                        <Amenity category="connectivity">WiFi</Amenity>
                        <Amenity category="recreation">Beach Access</Amenity>
                        <Amenity category="dining">Restaurant</Amenity>
                    </Amenities>
                    <Rate currency="USD">
                        <Total>399.99</Total>
                    </Rate>
                </Hotel>
            </SearchResponse>';

            $transformedData = $this->dataManager->transformHotelSearchResponse(
                $this->getVendorCode(),
                $mockXmlResponse
            );

            return VendorResponse::success(
                data: $transformedData,
                resultCount: count($transformedData),
                vendorTransactionId: 'ST_' . uniqid()
            );

        } catch (\Exception $e) {
            return VendorResponse::error(
                "StephensTravel error: {$e->getMessage()}",
                'VENDOR_ERROR'
            );
        }
    }

    public function searchFlights(SearchCriteria $criteria): VendorResponse
    {
        try {
            // Mock XML response for demonstration
            $mockXmlResponse = '<?xml version="1.0"?>
            <FlightSearchResponse>
                <Flight id="FLT_ST_001">
                    <Airline>ST</Airline>
                    <FlightNumber>ST456</FlightNumber>
                    <Departure>
                        <Airport>' . $criteria->origin . '</Airport>
                        <DateTime>' . $criteria->departureDate?->toISOString() . '</DateTime>
                    </Departure>
                    <Arrival>
                        <Airport>' . $criteria->destination . '</Airport>
                        <DateTime>' . $criteria->departureDate?->addHours(4)->toISOString() . '</DateTime>
                    </Arrival>
                    <Price currency="USD">
                        <Amount>599.99</Amount>
                    </Price>
                </Flight>
            </FlightSearchResponse>';

            $transformedData = $this->dataManager->transformFlightSearchResponse(
                $this->getVendorCode(),
                $mockXmlResponse
            );

            return VendorResponse::success(
                data: $transformedData,
                resultCount: count($transformedData),
                vendorTransactionId: 'ST_' . uniqid()
            );

        } catch (\Exception $e) {
            return VendorResponse::error(
                "StephensTravel error: {$e->getMessage()}",
                'VENDOR_ERROR'
            );
        }
    }
}