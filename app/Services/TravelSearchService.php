<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Vendors\Contracts\VendorInterface;
use App\DTOs\Vendor\SearchCriteria;
use App\Http\Resources\TravelSearchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Travel search service for multi-vendor aggregation
 * Orchestrates searches across multiple travel vendors with type safety
 */
class TravelSearchService
{
    /** @var Collection<VendorInterface> */
    private Collection $vendors;

    public function __construct()
    {
        $this->vendors = collect();
    }

    public function registerVendor(VendorInterface $vendor): self
    {
        $this->vendors->put($vendor->getVendorCode(), $vendor);
        return $this;
    }

    public function searchHotels(SearchCriteria $criteria): JsonResponse
    {
        return $this->performSearch('searchHotels', $criteria);
    }

    public function searchFlights(SearchCriteria $criteria): JsonResponse
    {
        return $this->performSearch('searchFlights', $criteria);
    }

    private function performSearch(string $method, SearchCriteria $criteria): JsonResponse
    {
        $results = [];
        $errors = [];
        $vendorsSearched = [];

        foreach ($this->getAvailableVendors() as $vendor) {
            try {
                $response = $vendor->$method($criteria);
                $vendorsSearched[] = $vendor->getVendorCode();

                if ($response->success) {
                    
                    $dataAsArrays = array_map(function ($dto) {
                        return $dto->toArray();
                    }, $response->data);
                    $results = array_merge($results, $dataAsArrays);
                } else {
                    $errors[$vendor->getVendorCode()] = $response->errorMessage;
                }
            } catch (\Exception $e) {
                $errors[$vendor->getVendorCode()] = $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'data' => TravelSearchResource::collection($results),
            'meta' => [
                'result_count' => count($results),
                'vendors_searched' => $vendorsSearched,
                'search_metadata' => [
                    'search_time' => now()->toISOString(),
                    'cache_ttl' => 300,
                ],
                'errors' => $errors
            ],
        ]);
    }

    private function getAvailableVendors(): Collection
    {
        return $this->vendors->filter(fn(VendorInterface $vendor): bool => $vendor->isAvailable());
    }
}
