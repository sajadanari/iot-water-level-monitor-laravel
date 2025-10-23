<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaterLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'level_cm' => $this->level_cm,
            'level_percentage' => $this->calculatePercentage(),
            'status' => $this->getStatus(),
            'raw_data' => $this->raw_data,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Calculate water level percentage based on tank capacity.
     * Assuming a standard tank height of 200cm for percentage calculation.
     */
    private function calculatePercentage(): int
    {
        $maxHeight = 200; // cm - adjust based on your tank specifications
        return min(100, max(0, round(($this->level_cm / $maxHeight) * 100)));
    }

    /**
     * Determine status based on water level.
     */
    private function getStatus(): string
    {
        $percentage = $this->calculatePercentage();
        
        if ($percentage >= 80) {
            return 'excellent';
        } elseif ($percentage >= 60) {
            return 'good';
        } elseif ($percentage >= 40) {
            return 'moderate';
        } elseif ($percentage >= 20) {
            return 'low';
        } else {
            return 'critical';
        }
    }
}
