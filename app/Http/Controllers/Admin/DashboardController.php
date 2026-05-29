<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Station;
use App\Models\DriverStationAuditLog;
use App\Models\Facility;


class DashboardController extends Controller
{
    public function index()
    {
        $totalDrivers   = User::where('role', 'driver')->count();
        $totalProviders = User::where('role', 'provider')->count();
        $totalStations  = Station::count();

        $pendingStations = Station::with(['user', 'connectors', 'facilities'])
            ->where('approval_status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $recentDriverStationLogs = DriverStationAuditLog::with(['driver', 'station'])
            ->pending()
            ->latest()
            ->limit(10)
            ->get();

        $facilitiesById = Facility::pluck('name', 'id');

        return view('admin.dashboard', compact(
            'totalDrivers',
            'totalProviders',
            'totalStations',
            'pendingStations',
            'recentDriverStationLogs',
            'facilitiesById'
        ));

    }
}
