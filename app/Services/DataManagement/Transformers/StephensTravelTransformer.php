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
use SimpleXMLElement;
use InvalidArgumentException;

/**
 * StephensTravel XML format transformer
 * Transforms XML API responses to standardized data format
 */
class StephensTravelTransformer implements DataTransformerInterface
{
    public function transformHotelSearchResponse(mixed $vendorData): array
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

    public function transformFlightSearchResponse(mixed $vendorData): array
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

    public function transformHotelDetails(mixed $vendorData): StandardizedHotel
    {
        $hotels = $this->transformHotelSearchResponse($vendorData);
        if (empty($hotels)) {
            throw new InvalidArgumentException('No hotel data found in vendor response');
        }
        return $hotels[0];
    }

    public function transformFlightDetails(mixed $vendorData): StandardizedFlight
    {
        $flights = $this->transformFlightSearchResponse($vendorData);
        if (empty($flights)) {
            throw new InvalidArgumentException('No flight data found in vendor response');
        }
        return $flights[0];
    }

    public function getVendorCode(): string
    {
        return 'STEPHENSTRAVEL';
    }

    public function getSupportedFormat(): string
    {
        return 'xml';
    }

    public function canTransform(mixed $vendorData): bool
    {
        if ($vendorData instanceof SimpleXMLElement) {
            return true;
        }

        if (is_string($vendorData)) {
            $xml = simplexml_load_string($vendorData);
            return $xml !== false;
        }

        return false;
    }

    private function parseXml(mixed $vendorData): SimpleXMLElement
    {
        if ($vendorData instanceof SimpleXMLElement) {
            return $vendorData;
        }

        if (is_string($vendorData)) {
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
        
        // Handle single amenity vs multiple amenities
        if (!is_array($amenities) && !($amenities instanceof \Traversable)) {
            $amenities = [$amenities];
        }

        $result = [];
        foreach ($amenities as $amenity) {
            $result[] = new Amenity(
                name: (string) $amenity,
                category: (string) $amenity['category'] ?? 'general',
            );
        }

        return $result;
    }
}