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
use SimpleXMLElement;
use InvalidArgumentException;

/**
 * StephensTravel vendor implementation
 * Handles XML API communication and data transformation
 */
class StephensTravelVendor implements VendorInterface
{
    public function __construct(
        private readonly HttpClient $httpClient,
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
        return !empty($this->apiKey) && !empty($this->baseUrl);
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

            $transformedData = $this->transformHotelSearchResponse($mockXmlResponse);

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

            $transformedData = $this->transformFlightSearchResponse($mockXmlResponse);

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

    private function transformHotelSearchResponse(mixed $vendorData): array
    {
        $xml = $this->parseXml($vendorData);
        $hotels = $xml->xpath('//Hotel') ?? [];

        return array_map(fn(SimpleXMLElement $hotel): StandardizedHotel => 
            new StandardizedHotel(
                externalId: (string) $hotel['id'] ?? '',
                vendorCode: $this->getVendorCode(),
                name: (string) $hotel->Name ?? '',
                description: (string) $hotel->Description ?? '',
                address: new Address(
                    street: (string) $hotel->Address->Street ?? '',
                    city: (string) $hotel->Address->City ?? '',
                    country: (string) $hotel->Address->Country ?? '',
                ),
                coordinates: new Coordinates(
                    latitude: (float) ($hotel->Coordinates->Latitude ?? 0),
                    longitude: (float) ($hotel->Coordinates->Longitude ?? 0),
                ),
                starRating: (int) ($hotel->StarRating ?? 0),
                amenities: $this->parseAmenities($hotel),
                availability: new Availability(
                    isAvailable: !empty($hotel->Rate),
                    price: new Price(
                        amount: (float) ($hotel->Rate->Total ?? 0),
                        currency: (string) ($hotel->Rate['currency'] ?? 'USD'),
                    ),
                ),
            ), 
            $hotels
        );
    }

    private function transformFlightSearchResponse(mixed $vendorData): array
    {
        $xml = $this->parseXml($vendorData);
        $flights = $xml->xpath('//Flight') ?? [];

        return array_map(fn(SimpleXMLElement $flight): StandardizedFlight => 
            new StandardizedFlight(
                externalId: (string) $flight['id'] ?? '',
                vendorCode: $this->getVendorCode(),
                airlineCode: (string) $flight->Airline ?? '',
                flightNumber: (string) $flight->FlightNumber ?? '',
                departureAirport: (string) $flight->Departure->Airport ?? '',
                arrivalAirport: (string) $flight->Arrival->Airport ?? '',
                departureDateTime: (string) $flight->Departure->DateTime ?? '',
                arrivalDateTime: (string) $flight->Arrival->DateTime ?? '',
                availability: new Availability(
                    isAvailable: !empty($flight->Price),
                    price: new Price(
                        amount: (float) ($flight->Price->Amount ?? 0),
                        currency: (string) ($flight->Price['currency'] ?? 'USD'),
                    ),
                ),
            ), 
            $flights
        );
    }

    private function parseXml(mixed $vendorData): SimpleXMLElement
    {
        if ($vendorData instanceof SimpleXMLElement) {
            return $vendorData;
        }

        if (is_string($vendorData)) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($vendorData);
            if ($xml === false) {
                throw new InvalidArgumentException('Invalid XML data provided');
            }
            return $xml;
        }

        throw new InvalidArgumentException('Data must be XML string or SimpleXMLElement');
    }

    /**
     * Parse amenities from XML, handling cases where no amenities exist
     * 
     * @param SimpleXMLElement $hotel
     * @return Amenity[]
     */
    private function parseAmenities(SimpleXMLElement $hotel): array
    {
        if (!isset($hotel->Amenities->Amenity)) {
            return [];
        }

        $amenities = $hotel->Amenities->Amenity;
        
        $result = [];
        foreach ($amenities as $amenity) {
            $result[] = new Amenity(
                name: (string) $amenity,
                category: (string) ($amenity['category'] ?? 'general'),
            );
        }

        return $result;
    }
}
