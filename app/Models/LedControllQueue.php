<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedControllQueue extends Model
{
    protected $table = 'led_controll_queues';

    protected $fillable = [
        'device_id',
        'command',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function scopeNotProcessed($query)
    {
        return $query->where('processed', false);
    }

    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

}
