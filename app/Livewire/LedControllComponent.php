<?php

namespace App\Livewire;

use App\Models\LedControllQueue;
use Livewire\Component;

class LedControllComponent extends Component
{

    public $ledStatus = false;
    public $device_id = null;

    public $devices = [];

    public $message = '';

    public function mount($devices = [])
    {
        $this->devices = $devices;
    }

    public function toggleLed()
    {
        if (!$this->device_id) {
            $this->message = 'Please select a device';
            return;
        }

        $this->ledStatus = !$this->ledStatus;

        LedControllQueue::create([
            'device_id' => $this->device_id,
            'command' => $this->ledStatus ? 'on' : 'off',
        ]);

        $this->message = "Command sent successfully: Turn " . ($this->ledStatus ? 'On' : 'Off') . " LED";

    }

    public function render()
    {
        return view('livewire.led-controll-component');
    }
}
