<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreWaterLevelRequest;
use App\Http\Resources\Api\WaterLevelResource;
use App\Models\WaterLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WaterLevelController extends Controller
{

    /**
     * Store a newly created water level reading from the IoT device.
     */
    public function store(StoreWaterLevelRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create the water level record
            $waterLevel = WaterLevel::create([
                'device_id' => $request->validated('device_id'),
                'level_cm' => $request->validated('level_cm'),
                'raw_data' => $request->all(),
                'timestamp' => $request->validated('timestamp'),
                'battery_level' => $request->validated('battery_level'),
                'temperature' => $request->validated('temperature'),
            ]);

            // Log successful data reception
            Log::info('Water level data stored successfully', [
                'water_level_id' => $waterLevel->id,
                'device_id' => $waterLevel->device_id,
                'level_cm' => $waterLevel->level_cm,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create the water level alert
            $waterLevel->createAlert();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Water level data recorded successfully.',
                'data' => new WaterLevelResource($waterLevel),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to store water level data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record water level data.',
            ], 500);
        }
    }

    /**
     * Display the latest water level reading for a specific device.
     */
    public function show(Request $request, string $deviceId): JsonResponse
    {
        try {
            $waterLevel = WaterLevel::getLatestForDevice($deviceId);

            if (!$waterLevel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No water level data found for device.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => new WaterLevelResource($waterLevel),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve water level data', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve water level data.',
            ], 500);
        }
    }

    /**
     * Display a listing of water level readings for a specific device.
     */
    public function index(Request $request, string $deviceId): JsonResponse
    {
        try {
            $hours = $request->query('hours', 24);
            $limit = min($request->query('limit', 100), 1000); // Max 1000 records

            $waterLevels = WaterLevel::forDevice($deviceId)
                ->recent($hours)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => WaterLevelResource::collection($waterLevels),
                'meta' => [
                    'device_id' => $deviceId,
                    'hours' => $hours,
                    'count' => $waterLevels->count(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve water level history', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve water level history.',
            ], 500);
        }
    }

    /**
     * Get statistics for a specific device.
     */
    public function stats(Request $request, string $deviceId): JsonResponse
    {
        try {
            $hours = $request->query('hours', 24);

            $stats = WaterLevel::forDevice($deviceId)
                ->recent($hours)
                ->selectRaw('
                    AVG(level_cm) as avg_level,
                    MIN(level_cm) as min_level,
                    MAX(level_cm) as max_level,
                    COUNT(*) as total_readings,
                    AVG(battery_level) as avg_battery,
                    AVG(temperature) as avg_temperature
                ')
                ->first();

            $latest = WaterLevel::getLatestForDevice($deviceId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'device_id' => $deviceId,
                    'period_hours' => $hours,
                    'statistics' => $stats,
                    'latest_reading' => $latest ? new WaterLevelResource($latest) : null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve water level statistics', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve water level statistics.',
            ], 500);
        }
    }

}