<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Station;
use App\Models\DriverStationAuditLog;


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
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalDrivers',
            'totalProviders',
            'totalStations',
            'pendingStations',
            'recentDriverStationLogs'
        ));

    }
}
