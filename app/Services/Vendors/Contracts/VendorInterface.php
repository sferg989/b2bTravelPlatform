<?php

declare(strict_types=1);

namespace App\Services\Vendors\Contracts;

use App\DTOs\Vendor\SearchCriteria;
use App\DTOs\Vendor\VendorResponse;

/**
 * Interface for all travel vendor integrations
 */
interface VendorInterface
{
    /**
     * Get the vendor code identifier
     */
    public function getVendorCode(): string;

    /**
     * Get the vendor display name
     */
    public function getVendorName(): string;

    /**
     * Check if the vendor service is available
     */
    public function isAvailable(): bool;

    /**
     * Search for hotels based on criteria
     */
    public function searchHotels(SearchCriteria $criteria): VendorResponse;

    /**
     * Search for flights based on criteria
     */
    public function searchFlights(SearchCriteria $criteria): VendorResponse;
}