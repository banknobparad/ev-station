<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'station_id',
        'type',
        'total',
        'status'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
