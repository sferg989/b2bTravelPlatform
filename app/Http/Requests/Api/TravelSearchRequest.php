<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for travel search endpoints
 */
class TravelSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $baseRules = [
            'max_results' => 'sometimes|integer|min:1|max:100',
            'filters' => 'sometimes|array',
        ];

        // Hotel-specific rules
        if ($this->is('*/hotels/search')) {
            return array_merge($baseRules, [
                'destination' => 'required|string|min:2',
                'check_in_date' => 'required|date|after_or_equal:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'guest_count' => 'sometimes|integer|min:1|max:10',
                'filters.star_rating' => 'sometimes|integer|min:1|max:5',
                'filters.price_min' => 'sometimes|numeric|min:0',
                'filters.price_max' => 'sometimes|numeric|min:0',
                'filters.amenities' => 'sometimes|array',
                'filters.amenities.*' => 'string',
            ]);
        }

        // Flight-specific rules
        if ($this->is('*/flights/search')) {
            return array_merge($baseRules, [
                'origin' => 'required|string|size:3', 
                'destination' => 'required|string|size:3', 
                'departure_date' => 'required|date|after_or_equal:today',
                'return_date' => 'required|date|after:departure_date',
                'passenger_count' => 'sometimes|integer|min:1|max:9',
                'filters.cabin_class' => 'sometimes|string|in:economy,premium_economy,business,first',
                'filters.airline_preference' => 'sometimes|array',
                'filters.airline_preference.*' => 'string|size:2',
                'filters.max_stops' => 'sometimes|integer|min:0|max:3',
                'filters.departure_time_preference' => 'sometimes|string|in:morning,afternoon,evening',
            ]);
        }

        return $baseRules;
    }

    public function messages(): array
    {
        return [
            'destination.required' => 'Destination is required for hotel searches.',
            'origin.required' => 'Origin airport code is required for flight searches.',
            'origin.size' => 'Origin must be a valid 3-letter IATA airport code.',
            'destination.size' => 'Destination must be a valid 3-letter IATA airport code.',
            'check_in_date.after_or_equal' => 'Check-in date must be today or in the future.',
            'check_out_date.after' => 'Check-out date must be after check-in date.',
            'departure_date.after_or_equal' => 'Departure date must be today or in the future.',
            'return_date.required' => 'Return date is required for flight searches.',
            'return_date.after' => 'Return date must be after departure date.',
            'max_results.max' => 'Maximum results cannot exceed 100.',
        ];
    }
}