<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDrivers   = User::where('role', 'driver')->count();
        $totalProviders = User::where('role', 'provider')->count();
        $totalStations  = Station::count();

        return view('admin.dashboard', compact(
            'totalDrivers',
            'totalProviders',
            'totalStations'
        ));
    }
}