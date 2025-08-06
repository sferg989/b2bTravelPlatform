<?php

declare(strict_types=1);

namespace App\Services\DataManagement\Contracts;

/**
 * Contract for all vendor data transformers
 * Defines the common interface for transforming vendor-specific data
 * into standardized format for the travel platform
 */
interface DataTransformerInterface
{
    /**
     * Transform vendor-specific hotel search response into standardized format
     * 
     * @param mixed $vendorData Raw data from vendor API (array, XML, JSON, etc.)
     * @return array Standardized hotel search results
     * @throws \App\Exceptions\DataTransformationException
     */
    public function transformHotelSearchResponse(mixed $vendorData): array;

    /**
     * Transform vendor-specific flight search response into standardized format
     * 
     * @param mixed $vendorData Raw data from vendor API (array, XML, JSON, etc.)
     * @return array Standardized flight search results
     * @throws \App\Exceptions\DataTransformationException
     */
    public function transformFlightSearchResponse(mixed $vendorData): array;

    /**
     * Transform detailed hotel information into standardized format
     * 
     * @param mixed $vendorData Raw hotel details from vendor API
     * @return array Standardized hotel details
     * @throws \App\Exceptions\DataTransformationException
     */
    public function transformHotelDetails(mixed $vendorData): array;

    /**
     * Transform detailed flight information into standardized format
     * 
     * @param mixed $vendorData Raw flight details from vendor API
     * @return array Standardized flight details
     * @throws \App\Exceptions\DataTransformationException
     */
    public function transformFlightDetails(mixed $vendorData): array;

    /**
     * Get the vendor code this transformer supports
     * 
     * @return string Unique vendor identifier (e.g., 'FERGUSONTRAVEL', 'STEPHENSTRAVEL')
     */
    public function getVendorCode(): string;

    /**
     * Get the data format this transformer supports
     * 
     * @return string Data format identifier (e.g., 'json', 'xml', 'graphql')
     */
    public function getSupportedFormat(): string;

    /**
     * Validate that the provided data can be transformed by this transformer
     * 
     * @param mixed $vendorData Raw data to validate
     * @return bool True if data can be transformed, false otherwise
     */
    public function canTransform(mixed $vendorData): bool;
}