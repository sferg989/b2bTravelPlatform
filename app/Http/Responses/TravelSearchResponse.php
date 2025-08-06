<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Typed API response for travel search endpoints
 * Provides structured typing for travel search results with metadata
 */
class TravelSearchResponse extends JsonResponse
{
    /**
     * Create a successful travel search response
     */
    public static function success(
        AnonymousResourceCollection $data,
        int $resultCount,
        array $vendorsSearched,
        array $searchMetadata,
        array $errors
    ): self {
        return new self([
            'success' => true,
            'data' => $data,
            'meta' => [
                'result_count' => $resultCount,
                'vendors_searched' => $vendorsSearched,
                'search_metadata' => $searchMetadata,
                'errors' => $errors
            ],
        ]);
    }

    /**
     * Create an error travel search response
     */
    public static function error(
        string $errorMessage,
        ?string $errorCode = null,
        int $statusCode = 400
    ): self {
        return new self([
            'success' => false,
            'error' => [
                'message' => $errorMessage,
                'code' => $errorCode,
            ],
            'data' => [],
            'meta' => [
                'result_count' => 0,
                'vendors_searched' => [],
                'search_metadata' => [],
                'errors' => [],
                'architecture_version' => 'v1',
                'data_management' => [
                    'registered_transformers' => [],
                ],
            ],
        ], $statusCode);
    }
}