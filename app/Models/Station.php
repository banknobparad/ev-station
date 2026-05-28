<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'lat',
        'lng',
        'image',
        'gallery_images',
        'open_time',
        'close_time',
        'approval_status',
    ];

    protected $casts = [
        'gallery_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function connectors()
    {
        return $this->hasMany(Connector::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'station_facility');
    }
}
