<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterLevel extends Model
{
    use HasFactory;

    protected $table = 'water_levels';

    public function alerts()
    {
        return $this->hasMany(WaterLevelAlerts::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'level_cm',
        'raw_data',
        'timestamp',
        'battery_level',
        'temperature',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'raw_data' => 'array',
        'timestamp' => 'datetime',
        'battery_level' => 'decimal:2',
        'temperature' => 'decimal:2',
        'level_cm' => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
    ];

    /**
     * Scope a query to only include readings from a specific device.
     */
    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope a query to only include recent readings.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to only include readings above a certain level.
     */
    public function scopeAboveLevel($query, float $level)
    {
        return $query->where('level_cm', '>', $level);
    }

    /**
     * Scope a query to only include readings below a certain level.
     */
    public function scopeBelowLevel($query, float $level)
    {
        return $query->where('level_cm', '<', $level);
    }

    /**
     * Get the latest reading for a specific device.
     */
    public static function getLatestForDevice(string $deviceId): ?self
    {
        return static::forDevice($deviceId)->latest()->first();
    }

    /**
     * Get average level for a device in the last N hours.
     */
    public static function getAverageLevel(string $deviceId, int $hours = 24): float
    {
        return static::forDevice($deviceId)
            ->recent($hours)
            ->avg('level_cm') ?? 0;
    }

    public function calculatePercentage($levelCm)
    {
        $maxHeight = 200;
        return min(100, max(0, round(($levelCm / $maxHeight) * 100)));
    }

    public function createAlert()
    {
        $percentage = $this->calculatePercentage($this->level_cm);
        if ($percentage <= 30) {
            $this->alerts()->create([
                'severity' => 'critical',
                'message' => 'Water level is critical at ' . $percentage . '%',
            ]);
        } elseif ($percentage <= 50) {
            $this->alerts()->create([
                'severity' => 'moderate',
                'message' => 'Water level is warning at ' . $percentage . '%',
            ]);
        }
        return $this;
    }
}