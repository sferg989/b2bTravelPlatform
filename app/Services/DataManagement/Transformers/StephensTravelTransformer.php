<?php

declare(strict_types=1);

namespace App\Services\DataManagement\Transformers;

use App\Services\DataManagement\Contracts\DataTransformerInterface;
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

        return array_map(fn(SimpleXMLElement $hotel): array => [
            'external_id' => (string) $hotel['id'] ?? '',
            'vendor_code' => $this->getVendorCode(),
            'name' => (string) $hotel->Name ?? '',
            'description' => (string) $hotel->Description ?? '',
            'address' => [
                'street' => (string) $hotel->Address->Street ?? '',
                'city' => (string) $hotel->Address->City ?? '',
                'country' => (string) $hotel->Address->Country ?? '',
            ],
            'coordinates' => [
                'latitude' => (float) ($hotel->Coordinates->Latitude ?? 0),
                'longitude' => (float) ($hotel->Coordinates->Longitude ?? 0),
            ],
            'star_rating' => (int) ($hotel->StarRating ?? 0),
            'amenities' => array_map(fn(SimpleXMLElement $amenity): array => [
                'name' => (string) $amenity,
                'category' => (string) $amenity['category'] ?? 'general',
            ], $hotel->Amenities->Amenity ?? []),
            'availability' => [
                'is_available' => !empty($hotel->Rate),
                'price' => [
                    'amount' => (float) ($hotel->Rate->Total ?? 0),
                    'currency' => (string) ($hotel->Rate['currency'] ?? 'USD'),
                ],
            ],
        ], $hotels);
    }

    public function transformFlightSearchResponse(mixed $vendorData): array
    {
        $xml = $this->parseXml($vendorData);
        $flights = $xml->xpath('//Flight') ?? [];

        return array_map(fn(SimpleXMLElement $flight): array => [
            'external_id' => (string) $flight['id'] ?? '',
            'vendor_code' => $this->getVendorCode(),
            'airline_code' => (string) $flight->Airline ?? '',
            'flight_number' => (string) $flight->FlightNumber ?? '',
            'departure_airport' => (string) $flight->Departure->Airport ?? '',
            'arrival_airport' => (string) $flight->Arrival->Airport ?? '',
            'departure_datetime' => (string) $flight->Departure->DateTime ?? '',
            'arrival_datetime' => (string) $flight->Arrival->DateTime ?? '',
            'availability' => [
                'is_available' => !empty($flight->Price),
                'price' => [
                    'amount' => (float) ($flight->Price->Amount ?? 0),
                    'currency' => (string) ($flight->Price['currency'] ?? 'USD'),
                ],
            ],
        ], $flights);
    }

    public function transformHotelDetails(mixed $vendorData): array
    {
        return $this->transformHotelSearchResponse($vendorData)[0] ?? [];
    }

    public function transformFlightDetails(mixed $vendorData): array
    {
        return $this->transformFlightSearchResponse($vendorData)[0] ?? [];
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
}