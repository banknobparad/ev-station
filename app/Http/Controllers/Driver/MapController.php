<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Station;

class MapController extends Controller
{
    public function index()
    {
        $stations = Station::with('connectors')
            ->where('approval_status', 'approved')
            ->get();

        return view('driver.map', compact('stations'));
    }

    public function show(Station $station)
    {
        $station->load('connectors', 'reviews.user');
        return view('driver.station', compact('station'));
    }
}
