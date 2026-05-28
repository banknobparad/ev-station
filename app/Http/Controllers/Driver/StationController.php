<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function create()
    {
        $facilities = \App\Models\Facility::all();
        return view('driver.stations.create', compact('facilities'));
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
            'approval_status'  => 'pending',
        ]);

        if ($request->has('facilities') && is_array($request->facilities)) {
            $station->facilities()->sync($request->facilities);
        }

        return redirect()->route('driver.account')
            ->with('success', 'ส่งคำขอเพิ่มสถานีแล้ว รอ Admin อนุมัติครับ');
    }
}

