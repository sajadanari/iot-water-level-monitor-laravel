<?php

namespace Database\Seeders;

use App\Models\WaterLevel;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WaterLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = ['device_001', 'device_002', 'device_003'];
        $now = Carbon::now();

        foreach ($devices as $deviceId) {
            // Generate readings for the last 7 days
            for ($i = 0; $i < 168; $i++) { // 168 hours = 7 days
                $timestamp = $now->copy()->subHours($i);
                
                // Simulate realistic water level patterns
                $baseLevel = rand(30, 80);
                $variation = rand(-10, 10);
                $levelCm = max(0, min(200, $baseLevel + $variation));
                
                // Simulate battery drain over time
                $batteryLevel = max(10, 100 - ($i * 0.5) + rand(-5, 5));
                
                // Simulate temperature variations
                $temperature = 20 + rand(-5, 15) + sin($i / 24) * 3;

                WaterLevel::create([
                    'device_id' => $deviceId,
                    'level_cm' => $levelCm,
                    'raw_data' => [
                        'sensor_type' => 'ultrasonic',
                        'signal_strength' => rand(70, 100),
                        'humidity' => rand(40, 80),
                    ],
                    'timestamp' => $timestamp,
                    'battery_level' => round($batteryLevel, 1),
                    'temperature' => round($temperature, 1),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Add some critical low level readings for testing alerts
        foreach ($devices as $deviceId) {
            WaterLevel::create([
                'device_id' => $deviceId,
                'level_cm' => rand(5, 15), // Critical low levels
                'raw_data' => [
                    'sensor_type' => 'ultrasonic',
                    'signal_strength' => rand(70, 100),
                    'humidity' => rand(40, 80),
                ],
                'timestamp' => $now->copy()->subMinutes(rand(5, 30)),
                'battery_level' => rand(20, 50),
                'temperature' => rand(18, 25),
                'created_at' => $now->copy()->subMinutes(rand(5, 30)),
                'updated_at' => $now->copy()->subMinutes(rand(5, 30)),
            ]);
        }

        // Create Alerts
        WaterLevel::all()->each(function ($waterLevel) {
            $waterLevel->createAlert();
        });
    }
}
