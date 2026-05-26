<?php

namespace App\Http\Controllers;

use App\Models\Station;

class HomeController extends Controller
{
    public function index()
    {
        $stations = Station::with('connectors')->get();
        $totalStations = $stations->count();
        return view('home', compact('stations', 'totalStations'));
    }
}