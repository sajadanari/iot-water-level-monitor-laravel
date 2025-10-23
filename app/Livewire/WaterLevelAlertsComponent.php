<?php

namespace App\Livewire;

use App\Models\WaterLevelAlerts;
use Livewire\Component;

class WaterLevelAlertsComponent extends Component
{
    public $alerts = [];
    public $alertThreshold = 'all';
    public $timeRange = 24;

    protected $listeners = ['refreshAlerts' => 'loadAlerts'];

    public function mount()
    {
        $this->loadAlerts();
    }

    public function loadAlerts()
    {
        $query = WaterLevelAlerts::where('dismissed', false);

        if ($this->alertThreshold !== 'all') {
            $query->where('severity', $this->alertThreshold);
        }

        $this->alerts = $query->orderBy('created_at', 'desc')->get()->take(10);
    }

    public function dismissAlert($alertId)
    {
        $alert = WaterLevelAlerts::find($alertId);
        $alert->dismissed = true;
        $alert->dismissed_at = now();
        $alert->save();
        $this->loadAlerts();
    }

    public function updatedAlertThreshold()
    {
        $this->loadAlerts();
    }

    public function updatedTimeRange()
    {
        $this->loadAlerts();
    }

    public function render()
    {
        return view('livewire.water-level-alerts-component');
    }
}
