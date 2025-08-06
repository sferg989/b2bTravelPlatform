<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TravelSearchRequest;
use App\Http\Resources\TravelSearchResource;
use App\Services\TravelSearchService;
use App\DTOs\Vendor\SearchCriteria;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for travel search operations
 * Provides unified access to multi-vendor travel inventory with data transformation
 */
class TravelController extends Controller
{
    public function __construct(
        private readonly TravelSearchService $travelSearchService
    ) {}

    /**
     * Search for hotels across all configured vendors
     * 
     * @endpoint GET /api/v1/travel/hotels/search
     */
    public function searchHotels(TravelSearchRequest $request): JsonResponse
    {
        $criteria = new SearchCriteria(
            searchType: 'hotel',
            destination: $request->validated('destination'),
            checkInDate: $request->date('check_in_date'),
            checkOutDate: $request->date('check_out_date'),
            guestCount: $request->integer('guest_count', 1),
            filters: (array) $request->input('filters', []),
            maxResults: $request->integer('max_results', 50),
        );

        $results = $this->travelSearchService->searchHotels($criteria);

        return response()->json([
            'success' => true,
            'data' => TravelSearchResource::collection($results['results']),
            'meta' => [
                'result_count' => $results['result_count'],
                'vendors_searched' => $results['vendors_searched'],
                'search_metadata' => $results['search_metadata'],
                'errors' => $results['errors'],
                'architecture_version' => 'v1',
                'data_management' => [
                    'registered_transformers' => $this->travelSearchService
                        ->getDataManager()
                        ->getSupportedVendors(),
                ],
            ],
        ]);
    }

    /**
     * Search for flights across all configured vendors
     * 
     * @endpoint GET /api/v1/travel/flights/search
     */
    public function searchFlights(TravelSearchRequest $request): JsonResponse
    {
        $criteria = new SearchCriteria(
            searchType: 'flight',
            origin: $request->validated('origin'),
            destination: $request->validated('destination'),
            departureDate: $request->date('departure_date'),
            returnDate: $request->date('return_date'),
            guestCount: $request->integer('passenger_count', 1),
            filters: (array) $request->input('filters', []),
            maxResults: $request->integer('max_results', 50),
        );

        $results = $this->travelSearchService->searchFlights($criteria);

        return response()->json([
            'success' => true,
            'data' => TravelSearchResource::collection($results['results']),
            'meta' => [
                'result_count' => $results['result_count'],
                'vendors_searched' => $results['vendors_searched'],
                'search_metadata' => $results['search_metadata'],
                'errors' => $results['errors'],
                'architecture_version' => 'v1',
                'data_management' => [
                    'registered_transformers' => $this->travelSearchService
                        ->getDataManager()
                        ->getSupportedVendors(),
                ],
            ],
        ]);
    }

    /**
     * Get system health and vendor status
     * 
     * @endpoint GET /api/v1/travel/health
     */
    public function health(): JsonResponse
    {
        $vendors = $this->travelSearchService->getRegisteredVendors();
        $dataManager = $this->travelSearchService->getDataManager();
        
        $vendorStatus = [];
        foreach ($vendors as $vendor) {
            $vendorStatus[$vendor->getVendorCode()] = [
                'name' => $vendor->getVendorName(),
                'available' => $vendor->isAvailable(),
                'has_transformer' => $dataManager->hasTransformer($vendor->getVendorCode()),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'architecture_version' => 'v1',
                'vendors' => $vendorStatus,
                'transformers' => $dataManager->getSupportedVendors(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}