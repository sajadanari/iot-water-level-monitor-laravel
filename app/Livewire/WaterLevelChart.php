<?php

namespace App\Livewire;

use App\Models\WaterLevel;
use Livewire\Component;
use Illuminate\Support\Js; // Required for safely passing PHP data to JavaScript

class WaterLevelChart extends Component
{
    // Public properties passed from the parent component or updated via wire:model
    public $deviceId;
    public $timeRange = 24;
    public $chartData = []; 

    // Color map for different device series
    private $colorMap = [
        'device_001' => ['#3b82f6', '#93c5fd'], // Blue
        'device_002' => ['#f59e0b', '#fcd34d'], // Yellow/Amber
        'device_003' => ['#10b981', '#6ee7b7'], // Green
        'default' => ['#6b7280', '#d1d5db'],  // Gray
    ];

    /**
     * Mount the component and initialize data.
     */
    public function mount($deviceId = null, $timeRange = 24)
    {
        $this->deviceId = $deviceId;
        $this->timeRange = $timeRange;
        $this->loadChartData();
    }

    /**
     * Automatically called when the 'timeRange' property is updated via wire:model.live.
     * This method triggers data reload and chart redrawing via dispatch.
     */
    public function updatedTimeRange()
    {
        $this->loadChartData();
    }
    
    /**
     * Automatically called when the 'deviceId' property is updated (from parent dashboard).
     */
    public function updatedDeviceId()
    {
        $this->loadChartData();
    }

    /**
     * Fetches and processes water level data for the chart.
     */
    public function loadChartData()
    {
        // 1. Build the query to fetch recent data based on $this->timeRange
        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours($this->timeRange))
            ->orderBy('created_at', 'asc');

        if ($this->deviceId && $this->deviceId !== 'all') {
            $query->where('device_id', $this->deviceId);
        }

        $readings = $query->get();

        // 2. Transform data into Chart.js Datasets format
        $datasets = $readings->groupBy('device_id')->map(function ($deviceReadings, $deviceId) {
            $colors = $this->colorMap[$deviceId] ?? $this->colorMap['default'];
            
            return [
                'label' => 'Device ' . $deviceId . ' Level (%)',
                'borderColor' => $colors[0],
                'backgroundColor' => $colors[1],
                'data' => $deviceReadings->map(function ($reading) {
                    $percentage = $this->calculatePercentage($reading->level_cm);
                    return [
                        // X-axis data for time series chart
                        'x' => $reading->created_at->format('Y-m-d H:i:s'), 
                        'y' => $percentage, // Y-axis data (percentage)
                    ];
                })->values()->toArray(),
                'tension' => 0.4, 
                'fill' => false,
                'pointRadius' => 2, 
            ];
        })->values()->toArray();
        
        $this->chartData = $datasets;
        
        // 3. Dispatch a Livewire event to update the JavaScript chart.
        // The timeRange is also sent to help JavaScript debug or update chart title if needed.
        $this->dispatch('chart-updated-' . $this->getId(), chartData: Js::from($this->chartData));
    }

    /**
     * Calculate water level percentage based on max height (200 cm).
     */
    private function calculatePercentage($levelCm)
    {
        $maxHeight = 200; // cm 
        return min(100, max(0, round(($levelCm / $maxHeight) * 100)));
    }

    /**
     * Render the component's view.
     */
    public function render()
    {
        return view('livewire.water-level-chart');
    }
}