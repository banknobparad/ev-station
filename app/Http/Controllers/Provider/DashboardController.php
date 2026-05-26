<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $stations = Station::where('user_id', Auth::id())->get();
        $totalStations = $stations->count();

        return view('provider.dashboard', compact('stations', 'totalStations'));
    }
}