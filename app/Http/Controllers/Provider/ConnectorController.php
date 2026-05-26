<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectorController extends Controller
{
    public function index(Station $station)
    {
        if ($station->user_id !== Auth::id()) abort(403);

        $connectors = $station->connectors;
        return view('provider.connectors.index', compact('station', 'connectors'));
    }

    public function create(Station $station)
    {
        if ($station->user_id !== Auth::id()) abort(403);
        return view('provider.connectors.create', compact('station'));
    }

    public function store(Request $request, Station $station)
    {
        if ($station->user_id !== Auth::id()) abort(403);

        $request->validate([
            'type'   => 'required|in:CCS2,CHAdeMO,Type2,GB/T',
            'total'  => 'required|integer|min:1',
            'status' => 'required|in:available,busy,maintenance',
        ]);

        Connector::create([
            'station_id' => $station->id,
            'type'       => $request->type,
            'total'      => $request->total,
            'status'     => $request->status,
        ]);

        return redirect()->route('provider.stations.connectors.index', $station)
            ->with('success', 'เพิ่มหัวชาร์จสำเร็จแล้วครับ');
    }

    public function update(Request $request, Station $station, Connector $connector)
    {
        if ($station->user_id !== Auth::id()) abort(403);

        $request->validate([
            'status' => 'required|in:available,busy,maintenance',
        ]);

        $connector->update(['status' => $request->status]);

        return redirect()->route('provider.stations.connectors.index', $station)
            ->with('success', 'อัปเดตสถานะสำเร็จแล้วครับ');
    }

    public function destroy(Station $station, Connector $connector)
    {
        if ($station->user_id !== Auth::id()) abort(403);

        $connector->delete();
        return redirect()->route('provider.stations.connectors.index', $station)
            ->with('success', 'ลบหัวชาร์จสำเร็จแล้วครับ');
    }
}
