<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DataManagement\DataManager;
use App\Services\DataManagement\Transformers\FergusontravelTransformer;
use App\Services\DataManagement\Transformers\StephensTravelTransformer;
use App\Services\TravelSearchService;
use App\Services\Vendors\V1\FergusontravelVendor;
use App\Services\Vendors\V1\StephensTravelVendor;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for vendor and data management services
 * Configures DataManager and vendor implementations for travel search functionality
 */
class VendorServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        // Register DataManager as singleton
        $this->app->singleton(DataManager::class, function () {
            $dataManager = new DataManager();
            
            // Register transformers
            $dataManager
                ->registerTransformer(new FergusontravelTransformer())
                ->registerTransformer(new StephensTravelTransformer());
            
            return $dataManager;
        });

        // Register TravelSearchService as singleton
        $this->app->singleton(TravelSearchService::class, function ($app) {
            return new TravelSearchService($app->make(DataManager::class));
        });

        // Register vendors
        $this->app->singleton(FergusontravelVendor::class, function ($app) {
            return new FergusontravelVendor(
                httpClient: $app->make(HttpClient::class),
                dataManager: $app->make(DataManager::class),
                clientId: config('vendors.fergusontravel.client_id', ''),
                clientSecret: config('vendors.fergusontravel.client_secret', ''),
                baseUrl: config('vendors.fergusontravel.base_url', ''),
                graphqlEndpoint: config('vendors.fergusontravel.graphql_endpoint', ''),
            );
        });

        $this->app->singleton(StephensTravelVendor::class, function ($app) {
            return new StephensTravelVendor(
                httpClient: $app->make(HttpClient::class),
                dataManager: $app->make(DataManager::class),
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