<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverStationAuditLog;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $reviewCount = $user->reviews()->count();
        $visitedCount = $user->reviews()->select('station_id')->distinct()->count();
        $recentComments = $user->reviews()->with('station')->latest()->limit(5)->get();
        $myStations = $user->stations()->latest()->get();

        $pendingAuditsByStation = DriverStationAuditLog::where('driver_id', $user->id)
            ->pending()
            ->get()
            ->keyBy('station_id');

        return view('driver.account', compact(
            'user',
            'reviewCount',
            'visitedCount',
            'recentComments',
            'myStations',
            'pendingAuditsByStation'
        ));
    }
}
