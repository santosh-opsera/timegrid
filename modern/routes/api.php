<?php

use App\Http\Controllers\Api\AvailabilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('businesses/{business}/services/{service}/dates', [AvailabilityController::class, 'dates']);
    Route::get('businesses/{business}/services/{service}/times/{date}', [AvailabilityController::class, 'times']);
});
