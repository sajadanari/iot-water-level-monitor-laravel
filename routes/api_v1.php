<?php

use App\Http\Controllers\Api\LedControllController;
use App\Http\Controllers\Api\WaterLevelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes (Loaded via RouteServiceProvider)
|--------------------------------------------------------------------------
| Routes in this file are automatically prefixed with '/api/v1'
| and use the 'api' middleware group.
*/

// Water Level API Routes
Route::prefix('water-levels')->group(function () {
    // Store new water level reading (for IoT devices) - uses IoT rate limiting
    Route::post('/', [WaterLevelController::class, 'store'])
        ->middleware('throttle:iot');

    // Get latest reading for a specific device
    Route::get('/{deviceId}', [WaterLevelController::class, 'show']);

    // Get reading history for a specific device
    Route::get('/{deviceId}/history', [WaterLevelController::class, 'index']);

    // Get statistics for a specific device
    Route::get('/{deviceId}/stats', [WaterLevelController::class, 'stats']);
});


// LED Control API Routes
Route::prefix('led-commands')->group(function () {
    // Get the earliest not proccesed led control queue record for a specific device
    Route::get('/get-command', [LedControllController::class, 'getCommand']);

    // Update the led control queue record as processed
    Route::post('/proccess-command', [LedControllController::class, 'processCommand']);
});
