<?php

declare(strict_types=1);

namespace App\DTOs\Vendor;

use Carbon\Carbon;

/**
 * Data transfer object for vendor search criteria
 */
class SearchCriteria
{
    public function __construct(
        public readonly string $searchType, // 'hotel', 'flight'
        public readonly ?string $destination = null,
        public readonly ?string $origin = null,
        public readonly ?Carbon $checkInDate = null,
        public readonly ?Carbon $checkOutDate = null,
        public readonly ?Carbon $departureDate = null,
        public readonly ?Carbon $returnDate = null,
        public readonly int $guestCount = 1,
        public readonly array $filters = [],
        public readonly int $maxResults = 100,
    ) {}

    public function toArray(): array
    {
        return [
            'search_type' => $this->searchType,
            'destination' => $this->destination,
            'origin' => $this->origin,
            'check_in_date' => $this->checkInDate?->toDateString(),
            'check_out_date' => $this->checkOutDate?->toDateString(),
            'departure_date' => $this->departureDate?->toDateString(),
            'return_date' => $this->returnDate?->toDateString(),
            'guest_count' => $this->guestCount,
            'filters' => $this->filters,
            'max_results' => $this->maxResults,
        ];
    }
}