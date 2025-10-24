<?php

namespace App\Livewire;

use App\Models\WaterLevel;
use Livewire\Component;
use Illuminate\Support\Js;

class WaterLevelChart extends Component
{
    public $deviceId;
    public $timeRange = 24;
    public $chartData = [];

    private $colorMap = [
        'device_001' => ['#3b82f6', '#93c5fd'], // Blue
        'device_002' => ['#f59e0b', '#fcd34d'], // Amber
        'device_003' => ['#10b981', '#6ee7b7'], // Green
        'default'    => ['#6b7280', '#d1d5db'], // Gray
    ];

    public function mount($deviceId = null, $timeRange = 6)
    {
        $this->deviceId = $deviceId;
        $this->timeRange = $timeRange;
        $this->loadChartData();
    }

    public function updatedDeviceId()
    {
        // Refresh data when device changes
        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Fetch data for selected time range
        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours((int)$this->timeRange))
            ->orderBy('created_at', 'asc');

        if ($this->deviceId && $this->deviceId !== 'all') {
            $query->where('device_id', $this->deviceId);
        }

        $readings = $query->get();

        // Transform to Chart.js format
        $datasets = $readings->groupBy('device_id')->map(function ($deviceReadings, $deviceId) {
            $colors = $this->colorMap[$deviceId] ?? $this->colorMap['default'];

            return [
                'label' => 'Device ' . $deviceId . ' Level (%)',
                'borderColor' => $colors[0],
                'backgroundColor' => $colors[1],
                'data' => $deviceReadings->map(function ($reading) {
                    $percentage = $this->calculatePercentage($reading->level_cm);
                    return [
                        'x' => $reading->created_at->format('Y-m-d H:i:s'),
                        'y' => $percentage,
                    ];
                })->values()->toArray(),
                'tension' => 0.4,
                'fill' => false,
                'pointRadius' => 2,
            ];
        })->values()->toArray();

        $this->chartData = $datasets;
    }

    private function calculatePercentage($levelCm)
    {
        $maxHeight = 200; // cm
        return min(100, max(0, round(($levelCm / $maxHeight) * 100)));
    }

    public function render()
    {
        return view('livewire.water-level-chart');
    }
}
