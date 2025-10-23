<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterLevelAlerts extends Model
{
    protected $fillable = [
        'water_level_id',
        'severity',
        'message',
        'dismissed',
        'dismissed_at',
    ];

    public function waterLevel()
    {
        return $this->belongsTo(WaterLevel::class);
    }
}
