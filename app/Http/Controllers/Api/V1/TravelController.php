<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TravelSearchRequest;
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

        return $this->travelSearchService->searchHotels($criteria);
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

        return $this->travelSearchService->searchFlights($criteria);
    }

}