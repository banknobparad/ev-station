<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::where('user_id', Auth::id())->get();
        return view('provider.stations.index', compact('stations'));
    }

    public function create()
    {
        $facilities = \App\Models\Facility::all();
        return view('provider.stations.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'required|string',
            'lat'        => 'required|numeric',
            'lng'        => 'required|numeric',
            'open_time'  => 'nullable',
            'close_time' => 'nullable',
            'image'      => 'nullable|image|max:2048',
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stations', 'public');
        }

        $station = Station::create([
            'user_id'           => Auth::id(),
            'name'              => $request->name,
            'address'           => $request->address,
            'lat'               => $request->lat,
            'lng'               => $request->lng,
            'open_time'         => $request->open_time,
            'close_time'        => $request->close_time,
            'image'             => $imagePath,
            'approval_status'  => 'approved',
        ]);

        if ($request->has('facilities') && is_array($request->facilities)) {
            $station->facilities()->sync($request->facilities);
        }

        return redirect()->route('provider.stations.index')
                         ->with('success', 'เพิ่มสถานีสำเร็จแล้วครับ');
    }

    public function edit(Station $station)
    {
        // กันไม่ให้ Provider แก้สถานีของคนอื่น
        if ($station->user_id !== Auth::id()) {
            abort(403);
        }
        $facilities = \App\Models\Facility::all();
        return view('provider.stations.edit', compact('station', 'facilities'));
    }

    public function update(Request $request, Station $station)
    {
        if ($station->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'required|string',
            'lat'        => 'required|numeric',
            'lng'        => 'required|numeric',
            'open_time'  => 'nullable',
            'close_time' => 'nullable',
            'image'      => 'nullable|image|max:2048',
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
        ]);

        $imagePath = $station->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stations', 'public');
        }

        $station->update([
            'name'       => $request->name,
            'address'    => $request->address,
            'lat'        => $request->lat,
            'lng'        => $request->lng,
            'open_time'  => $request->open_time,
            'close_time' => $request->close_time,
            'image'      => $imagePath,
        ]);

        if ($request->has('facilities') && is_array($request->facilities)) {
            $station->facilities()->sync($request->facilities);
        }

        return redirect()->route('provider.stations.index')
                         ->with('success', 'แก้ไขสถานีสำเร็จแล้วครับ');
    }

    public function destroy(Station $station)
    {
        if ($station->user_id !== Auth::id()) {
            abort(403);
        }

        $station->delete();
        return redirect()->route('provider.stations.index')
                         ->with('success', 'ลบสถานีสำเร็จแล้วครับ');
    }
}
