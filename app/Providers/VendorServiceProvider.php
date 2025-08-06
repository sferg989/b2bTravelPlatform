<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TravelSearchService;
use App\Services\Vendors\V1\FergusontravelVendor;
use App\Services\Vendors\V1\StephensTravelVendor;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for vendor and data management services
 * Configures vendor implementations for travel search functionality
 */
class VendorServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        // Register TravelSearchService as singleton
        $this->app->singleton(TravelSearchService::class, function () {
            return new TravelSearchService();
        });

        // Register vendors
        $this->app->singleton(FergusontravelVendor::class, function ($app) {
            return new FergusontravelVendor(
                httpClient: $app->make(HttpClient::class),
                clientId: config('vendors.fergusontravel.client_id', ''),
                clientSecret: config('vendors.fergusontravel.client_secret', ''),
                baseUrl: config('vendors.fergusontravel.base_url', ''),
            );
        });

        $this->app->singleton(StephensTravelVendor::class, function ($app) {
            return new StephensTravelVendor(
                httpClient: $app->make(HttpClient::class),
                apiKey: config('vendors.stephenstravel.api_key', ''),
                sharedSecret: config('vendors.stephenstravel.shared_secret', ''),
                baseUrl: config('vendors.stephenstravel.base_url', ''),
            );
        });
    }

    /**
     * Bootstrap application services
     */
    public function boot(): void
    {
        $searchService = $this->app->make(TravelSearchService::class);
        
        // Register vendors with the search service
        $searchService
            ->registerVendor($this->app->make(FergusontravelVendor::class))
            ->registerVendor($this->app->make(StephensTravelVendor::class));
    }
}
