<?php

declare(strict_types=1);

namespace App\Services\DataManagement;

use App\Services\DataManagement\Contracts\DataTransformerInterface;
use App\DTOs\StandardizedData\StandardizedHotel;
use App\DTOs\StandardizedData\StandardizedFlight;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Central data management service that orchestrates all vendor data transformers
 * Implements the Strategy pattern for vendor-specific data transformation
 */
class DataManager
{
    /** @var Collection<string, DataTransformerInterface> */
    private Collection $transformers;

    public function __construct()
    {
        $this->transformers = collect();
    }

    /**
     * Register a new data transformer
     * 
     * @param DataTransformerInterface $transformer The transformer to register
     * @return self For method chaining
     */
    public function registerTransformer(DataTransformerInterface $transformer): self
    {
        $this->transformers->put($transformer->getVendorCode(), $transformer);
        
        return $this;
    }

    /**
     * Transform hotel search response using appropriate transformer
     * 
     * @param string $vendorCode Vendor identifier
     * @param mixed $vendorData Raw vendor data
     * @return StandardizedHotel[] Standardized hotel data
     * @throws InvalidArgumentException If vendor transformer not found
     */
    public function transformHotelSearchResponse(string $vendorCode, mixed $vendorData): array
    {
        $transformer = $this->getTransformer($vendorCode);
        
        if (!$transformer->canTransform($vendorData)) {
            throw new InvalidArgumentException(
                "Transformer for vendor '{$vendorCode}' cannot process the provided data"
            );
        }

        return $transformer->transformHotelSearchResponse($vendorData);
    }

    /**
     * Transform flight search response using appropriate transformer
     * 
     * @param string $vendorCode Vendor identifier
     * @param mixed $vendorData Raw vendor data
     * @return StandardizedFlight[] Standardized flight data
     * @throws InvalidArgumentException If vendor transformer not found
     */
    public function transformFlightSearchResponse(string $vendorCode, mixed $vendorData): array
    {
        $transformer = $this->getTransformer($vendorCode);
        
        if (!$transformer->canTransform($vendorData)) {
            throw new InvalidArgumentException(
                "Transformer for vendor '{$vendorCode}' cannot process the provided data"
            );
        }

        return $transformer->transformFlightSearchResponse($vendorData);
    }

    /**
     * Transform hotel details using appropriate transformer
     * 
     * @param string $vendorCode Vendor identifier
     * @param mixed $vendorData Raw vendor data
     * @return StandardizedHotel Standardized hotel data
     * @throws InvalidArgumentException If vendor transformer not found
     */
    public function transformHotelDetails(string $vendorCode, mixed $vendorData): StandardizedHotel
    {
        $transformer = $this->getTransformer($vendorCode);
        
        if (!$transformer->canTransform($vendorData)) {
            throw new InvalidArgumentException(
                "Transformer for vendor '{$vendorCode}' cannot process the provided data"
            );
        }

        return $transformer->transformHotelDetails($vendorData);
    }

    /**
     * Transform flight details using appropriate transformer
     * 
     * @param string $vendorCode Vendor identifier
     * @param mixed $vendorData Raw vendor data
     * @return StandardizedFlight Standardized flight data
     * @throws InvalidArgumentException If vendor transformer not found
     */
    public function transformFlightDetails(string $vendorCode, mixed $vendorData): StandardizedFlight
    {
        $transformer = $this->getTransformer($vendorCode);
        
        if (!$transformer->canTransform($vendorData)) {
            throw new InvalidArgumentException(
                "Transformer for vendor '{$vendorCode}' cannot process the provided data"
            );
        }

        return $transformer->transformFlightDetails($vendorData);
    }

    /**
     * Get all registered transformers
     * 
     * @return Collection<string, DataTransformerInterface>
     */
    public function getRegisteredTransformers(): Collection
    {
        return $this->transformers;
    }

    /**
     * Check if a transformer is registered for a vendor
     * 
     * @param string $vendorCode Vendor identifier
     * @return bool True if transformer exists
     */
    public function hasTransformer(string $vendorCode): bool
    {
        return $this->transformers->has($vendorCode);
    }

    /**
     * Get supported vendor codes
     * 
     * @return array List of supported vendor codes
     */
    public function getSupportedVendors(): array
    {
        return $this->transformers->keys()->toArray();
    }

    /**
     * Get transformer for specific vendor
     * 
     * @param string $vendorCode Vendor identifier
     * @return DataTransformerInterface
     * @throws InvalidArgumentException If transformer not found
     */
    private function getTransformer(string $vendorCode): DataTransformerInterface
    {
        if (!$this->hasTransformer($vendorCode)) {
            throw new InvalidArgumentException(
                "No transformer registered for vendor: {$vendorCode}. " .
                "Available vendors: " . implode(', ', $this->getSupportedVendors())
            );
        }

        return $this->transformers->get($vendorCode);
    }
}