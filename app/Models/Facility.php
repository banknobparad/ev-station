<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['name', 'icon'];

    public function stations()
    {
        return $this->belongsToMany(Station::class, 'station_facility');
    }
}
