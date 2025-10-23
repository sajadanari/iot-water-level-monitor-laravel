<?php

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


// Legacy endpoint for backward compatibility - uses IoT rate limiting
Route::post('/data/receive', [WaterLevelController::class, 'store'])
    ->middleware('throttle:iot');