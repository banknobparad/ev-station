<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class StationApprovalController extends Controller
{
    public function indexPending()
    {
        $pendingStations = Station::with('user')
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        return view('admin.stations.pending', compact('pendingStations'));
    }


    public function approve(Station $station)
    {
        $station->update([
            'approval_status' => 'approved',
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'อนุมัติสถานีสำเร็จแล้วครับ');
    }

    public function reject(Station $station, Request $request)
    {
        // สำหรับเวอร์ชันนี้: ปฏิเสธ = ลบสถานีทิ้ง
        $station->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', 'ปฏิเสธสถานีแล้วครับ');
    }
}

