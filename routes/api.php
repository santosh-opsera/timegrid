<?php

declare(strict_types=1);

use App\Http\Controllers\API\AvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('throttle:120,1')
    ->controller(AvailabilityController::class)
    ->group(function (): void {
        Route::get('vacancies/{businessId}/{serviceId}', 'getDates');
        Route::get('vacancies/{businessId}/{serviceId}/{date}', 'getTimes');
    });
