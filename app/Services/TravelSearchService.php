<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Vendors\Contracts\VendorInterface;
use App\Services\DataManagement\DataManager;
use App\DTOs\Vendor\SearchCriteria;
use Illuminate\Support\Collection;

/**
 * Travel search service for multi-vendor aggregation
 * Orchestrates searches across multiple travel vendors with type safety
 */
class TravelSearchService
{
    /** @var Collection<VendorInterface> */
    private Collection $vendors;

    public function __construct(
        private readonly DataManager $dataManager
    ) {
        $this->vendors = collect();
    }

    public function registerVendor(VendorInterface $vendor): self
    {
        $this->vendors->put($vendor->getVendorCode(), $vendor);
        return $this;
    }

    public function searchHotels(SearchCriteria $criteria): array
    {
        return $this->performSearch('searchHotels', $criteria);
    }

    public function searchFlights(SearchCriteria $criteria): array
    {
        return $this->performSearch('searchFlights', $criteria);
    }

    private function performSearch(string $method, SearchCriteria $criteria): array
    {
        $results = [];
        $errors = [];
        $vendorsSearched = [];

        foreach ($this->getAvailableVendors() as $vendor) {
            try {
                $response = $vendor->$method($criteria);
                $vendorsSearched[] = $vendor->getVendorCode();

                if ($response->success) {
                    $results = array_merge($results, $response->data);
                } else {
                    $errors[$vendor->getVendorCode()] = $response->errorMessage;
                }
            } catch (\Exception $e) {
                $errors[$vendor->getVendorCode()] = $e->getMessage();
            }
        }

        return [
            'results' => $results,
            'result_count' => count($results),
            'vendors_searched' => $vendorsSearched,
            'errors' => $errors,
            'search_metadata' => [
                'search_time' => now()->toISOString(),
                'cache_ttl' => 300,
            ],
        ];
    }

    private function getAvailableVendors(): Collection
    {
        return $this->vendors->filter(fn(VendorInterface $vendor): bool => $vendor->isAvailable());
    }

    public function getRegisteredVendors(): Collection
    {
        return $this->vendors;
    }

    public function getDataManager(): DataManager
    {
        return $this->dataManager;
    }
}