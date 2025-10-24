<?php

namespace App\Livewire;

use App\Models\WaterLevel;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class WaterLevelDashboard extends Component
{
    use WithPagination;

    public $selectedDevice = 'all';
    public $timeRange = 24; // hours
    public $autoRefresh = true;
    public $refreshInterval = 5; // seconds
    public $showAlerts = true;
    public $alertThreshold = 'all'; // percentage

    protected $listeners = ['refreshData' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // This method will be called when data needs to be refreshed
        // The component will automatically re-render
        $this->resetPage();
        $this->dispatch('refreshAlerts');
    }

    public function updatedSelectedDevice()
    {
        $this->resetPage();
        // Send event to the WaterLevelChart component
        $this->dispatch('updateDevice', deviceId: $this->selectedDevice)
            ->to(WaterLevelChart::class);
    }

    public function updatedTimeRange()
    {
        $this->resetPage();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function refreshData()
    {
        $this->loadData();
    }

    public function getDevicesProperty()
    {
        return WaterLevel::select('device_id')
            ->distinct()
            ->orderBy('device_id')
            ->pluck('device_id');
    }

    public function getLatestReadingsProperty()
    {
        $query = WaterLevel::with([])
            ->select('device_id')
            ->selectRaw('MAX(created_at) as latest_reading')
            ->selectRaw('(SELECT level_cm FROM water_levels w2 WHERE w2.device_id = water_levels.device_id ORDER BY created_at DESC LIMIT 1) as level_cm')
            ->selectRaw('(SELECT battery_level FROM water_levels w2 WHERE w2.device_id = water_levels.device_id ORDER BY created_at DESC LIMIT 1) as battery_level')
            ->selectRaw('(SELECT temperature FROM water_levels w2 WHERE w2.device_id = water_levels.device_id ORDER BY created_at DESC LIMIT 1) as temperature')
            ->groupBy('device_id');

        if ($this->selectedDevice !== 'all') {
            $query->where('device_id', $this->selectedDevice);
        }

        return $query->get()->map(function ($device) {
            $percentage = $this->calculatePercentage($device->level_cm);
            return [
                'device_id' => $device->device_id,
                'level_cm' => $device->level_cm,
                'level_percentage' => $percentage,
                'status' => $this->getStatus($percentage),
                'battery_level' => $device->battery_level,
                'temperature' => $device->temperature,
                'last_reading' => Carbon::parse($device->latest_reading)->diffForHumans(),
                'is_online' => Carbon::parse($device->latest_reading)->isAfter(now()->subMinutes(10)),
            ];
        });
    }

    public function getRecentReadingsProperty()
    {
        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours($this->timeRange))
            ->orderBy('created_at', 'desc');

        if ($this->selectedDevice !== 'all') {
            $query->where('device_id', $this->selectedDevice);
        }

        return $query->paginate(20);
    }

    public function getStatisticsProperty()
    {
        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours($this->timeRange));

        if ($this->selectedDevice !== 'all') {
            $query->where('device_id', $this->selectedDevice);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_readings,
            AVG(level_cm) as avg_level,
            MIN(level_cm) as min_level,
            MAX(level_cm) as max_level,
            AVG(battery_level) as avg_battery,
            AVG(temperature) as avg_temperature
        ')->first();

        return [
            'total_readings' => $stats->total_readings ?? 0,
            'avg_level' => round($stats->avg_level ?? 0, 2),
            'min_level' => round($stats->min_level ?? 0, 2),
            'max_level' => round($stats->max_level ?? 0, 2),
            'avg_battery' => round($stats->avg_battery ?? 0, 1),
            'avg_temperature' => round($stats->avg_temperature ?? 0, 1),
        ];
    }

    public function getAlertsProperty()
    {
        if (!$this->showAlerts) {
            return collect();
        }

        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours($this->timeRange));

        if ($this->selectedDevice !== 'all') {
            $query->where('device_id', $this->selectedDevice);
        }

        return $query->get()->filter(function ($reading) {
            $percentage = $this->calculatePercentage($reading->level_cm);
            return $percentage <= $this->alertThreshold;
        })->map(function ($reading) {
            $percentage = $this->calculatePercentage($reading->level_cm);
            return [
                'device_id' => $reading->device_id,
                'level_cm' => $reading->level_cm,
                'level_percentage' => $percentage,
                'status' => $this->getStatus($percentage),
                'created_at' => $reading->created_at,
                'severity' => $percentage <= 10 ? 'critical' : ($percentage <= 20 ? 'warning' : 'info'),
            ];
        })->take(10);
    }

    public function getChartDataProperty()
    {
        $query = WaterLevel::query()
            ->where('created_at', '>=', now()->subHours($this->timeRange))
            ->orderBy('created_at', 'asc');

        if ($this->selectedDevice !== 'all') {
            $query->where('device_id', $this->selectedDevice);
        }

        $readings = $query->get();

        $chartData = $readings->groupBy('device_id')->map(function ($deviceReadings) {
            return $deviceReadings->map(function ($reading) {
                return [
                    'x' => $reading->created_at->format('Y-m-d H:i:s'),
                    'y' => $this->calculatePercentage($reading->level_cm),
                    'level_cm' => $reading->level_cm,
                ];
            })->values();
        });

        return $chartData;
    }

    private function calculatePercentage($levelCm)
    {
        $maxHeight = config('myapp.maxHeight'); // cm - adjust based on your tank specifications
        return min(100, max(0, round(($levelCm / $maxHeight) * 100)));
    }

    private function getStatus($percentage)
    {
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

    public function render()
    {
        return view('livewire.water-level-dashboard', [
            'devices' => $this->devices,
            'latestReadings' => $this->latestReadings,
            'recentReadings' => $this->recentReadings,
            'statistics' => $this->statistics,
            'alerts' => $this->alerts,
            'chartData' => $this->chartData,
        ]);
    }
}
