<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TravelController;

/*
|--------------------------------------------------------------------------
| Travel API v1 Routes
|--------------------------------------------------------------------------
|
| These routes provide access to multi-vendor travel inventory for
| travel advisors and client applications.
|
*/

Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
    
    // Hotel search
    Route::get('travel/hotels/search', [TravelController::class, 'searchHotels'])
        ->name('api.v1.travel.hotels.search');

    // Flight search
    Route::get('travel/flights/search', [TravelController::class, 'searchFlights'])
        ->name('api.v1.travel.flights.search');

});

