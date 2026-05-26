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
        'open_time',
        'close_time'
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
}
