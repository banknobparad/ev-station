<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Station;


class DriverStationAuditLog extends Model
{
    protected $table = 'driver_station_audit_logs';

    protected $fillable = [
        'driver_id',
        'station_id',
        'action',
        'reason',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
}

