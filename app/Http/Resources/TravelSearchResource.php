<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for travel search results
 * Standardizes the output format for advisor consumption
 */
class TravelSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['external_id'],
            'vendor_code' => $this->resource['vendor_code'],
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'location' => [
                'address' => $this->formatAddress($this->resource['address'] ?? []),
                'coordinates' => $this->resource['coordinates'] ?? null,
                'city' => $this->resource['address']['city'] ?? null,
                'country' => $this->resource['address']['country'] ?? null,
            ],
            'rating' => $this->formatRating(),
            'amenities' => $this->formatAmenities($this->resource['amenities'] ?? []),
            'images' => $this->formatImages($this->resource['images'] ?? []),
            'pricing' => $this->formatPricing($this->resource['availability'] ?? []),
            'availability' => $this->formatAvailability($this->resource['availability'] ?? []),
            'policies' => $this->formatPolicies($this->resource['policies'] ?? []),
            'rooms' => $this->when(
                isset($this->resource['rooms']),
                fn() => $this->formatRooms($this->resource['rooms'])
            ),
            'flight_details' => $this->when(
                isset($this->resource['airline_code']),
                fn() => $this->formatFlightDetails()
            ),
        ];
    }

    private function formatAddress(array $address): string
    {
        $parts = array_filter([
            $address['street'] ?? '',
            $address['city'] ?? '',
            $address['country'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    private function formatRating(): ?array
    {
        if (!isset($this->resource['star_rating'])) {
            return null;
        }

        return [
            'stars' => (int) $this->resource['star_rating'],
            'max_stars' => 5,
            'display' => str_repeat('★', (int) $this->resource['star_rating']) . 
                        str_repeat('☆', 5 - (int) $this->resource['star_rating']),
        ];
    }

    private function formatAmenities(array $amenities): array
    {
        $formatted = [];
        $grouped = [];

        foreach ($amenities as $amenity) {
            $category = $amenity['category'] ?? 'general';
            $grouped[$category][] = $amenity['name'] ?? $amenity;
        }

        foreach ($grouped as $category => $items) {
            $formatted[] = [
                'category' => ucfirst(str_replace('_', ' ', $category)),
                'items' => array_values(array_unique($items)),
            ];
        }

        return $formatted;
    }

    private function formatImages(array $images): array
    {
        return array_map(function ($image) {
            return [
                'url' => $image['url'],
                'caption' => $image['caption'] ?? '',
                'type' => $image['type'] ?? 'photo',
                'thumbnail_url' => $this->generateThumbnailUrl($image['url']),
            ];
        }, array_slice($images, 0, 10)); // Limit to 10 images
    }

    private function formatPricing(array $availability): ?array
    {
        if (!isset($availability['price'])) {
            return null;
        }

        $price = $availability['price'];

        return [
            'total_amount' => (float) $price['amount'],
            'currency' => $price['currency'] ?? 'USD',
            'formatted_price' => $this->formatCurrency($price['amount'], $price['currency'] ?? 'USD'),
            'breakdown' => [
                'base_rate' => (float) ($price['breakdown']['base_rate'] ?? $price['amount']),
                'taxes' => (float) ($price['breakdown']['taxes'] ?? 0),
                'fees' => (float) ($price['breakdown']['fees'] ?? 0),
            ],
            'per_night' => $this->when(
                isset($this->resource['availability']['price']),
                fn() => $this->calculatePerNightRate($price['amount'])
            ),
        ];
    }

    private function formatAvailability(array $availability): array
    {
        return [
            'is_available' => $availability['is_available'] ?? true,
            'rooms_available' => $availability['rooms_available'] ?? null,
            'last_updated' => now()->toISOString(),
            'is_refundable' => $availability['is_refundable'] ?? false,
            'cancellation_deadline' => $availability['cancellation_deadline'] ?? null,
        ];
    }

    private function formatPolicies(array $policies): array
    {
        return [
            'check_in_time' => $policies['check_in_time'] ?? null,
            'check_out_time' => $policies['check_out_time'] ?? null,
            'cancellation' => $policies['cancellation'] ?? null,
            'age_restrictions' => $policies['age_restrictions'] ?? null,
            'pet_policy' => $policies['pet_policy'] ?? null,
        ];
    }

    private function formatRooms(array $rooms): array
    {
        return array_map(function ($room) {
            return [
                'id' => $room['external_room_id'],
                'name' => $room['name'],
                'type' => $room['type'] ?? 'standard',
                'max_occupancy' => $room['max_occupancy'] ?? 2,
                'bed_configuration' => $this->formatBedConfiguration($room),
                'size_sqm' => $room['size_sqm'] ?? null,
                'amenities' => array_column($room['amenities'] ?? [], 'name'),
                'available_count' => $room['available_count'] ?? 1,
            ];
        }, $rooms);
    }



    private function formatFlightDetails(): array
    {
        return [
            'airline_code' => $this->resource['airline_code'],
            'flight_number' => $this->resource['flight_number'],
            'departure' => [
                'airport' => $this->resource['departure_airport'],
                'datetime' => $this->resource['departure_datetime'],
            ],
            'arrival' => [
                'airport' => $this->resource['arrival_airport'],
                'datetime' => $this->resource['arrival_datetime'],
            ],
            'duration_minutes' => $this->resource['duration_minutes'] ?? null,
            'aircraft_type' => $this->resource['aircraft_type'] ?? null,
            'is_direct' => $this->resource['is_direct'] ?? true,
            'stops_count' => $this->resource['stops_count'] ?? 0,
        ];
    }

    private function formatBedConfiguration(array $room): string
    {
        $bedCount = $room['bed_count'] ?? 1;
        $bedType = $room['bed_type'] ?? 'unknown';

        return "{$bedCount} {$bedType}" . ($bedCount > 1 ? 's' : '');
    }

    private function truncateDescription(string $description, int $maxLength = 200): string
    {
        if (strlen($description) <= $maxLength) {
            return $description;
        }

        return substr($description, 0, $maxLength) . '...';
    }

    private function generateThumbnailUrl(string $originalUrl): string
    {
        // Mock thumbnail generation - would implement actual thumbnail service
        return str_replace('.jpg', '_thumb.jpg', $originalUrl);
    }

    private function formatCurrency(float $amount, string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        
        return $symbol . number_format($amount, 2);
    }

    private function calculatePerNightRate(float $totalAmount): float
    {
        // This would need access to check-in/check-out dates to calculate properly
        // For now, return the total amount as a placeholder
        return $totalAmount;
    }

    private function getName(): string
    {
        // Check if this is flight data (has airline_code) or hotel data (has name)
        if (isset($this->resource['airline_code'])) {
            return $this->resource['airline_code'] . ' ' . $this->resource['flight_number'];
        }
        
        return $this->resource['name'] ?? '';
    }

    private function getDescription(): string
    {
        // For flights, create a description from route information
        if (isset($this->resource['airline_code'])) {
            $route = ($this->resource['departure_airport'] ?? '') . ' → ' . ($this->resource['arrival_airport'] ?? '');
            return "Flight from {$route}";
        }
        
        return $this->truncateDescription($this->resource['description'] ?? '');
    }
}