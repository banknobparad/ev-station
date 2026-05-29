<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Station\DeleteDriverStationRequest;
use App\Http\Requests\Station\StoreDriverStationRequest;
use App\Http\Requests\Station\UpdateDriverStationRequest;
use App\Models\DriverStationAuditLog;
use App\Models\Facility;
use App\Models\Station;
use App\Services\StationService;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function __construct(private readonly StationService $stations)
    {
    }

    public function create()
    {
        return view('driver.stations.create', [
            'facilities' => Facility::all(),
        ]);
    }

    public function store(StoreDriverStationRequest $request)
    {
        $this->stations->createForDriver(Auth::user(), $request->validated(), $request);

        return redirect()->route('driver.account')
            ->with('success', 'ส่งคำขอเพิ่มสถานีแล้ว รอ Admin อนุมัติครับ');
    }

    public function edit(Station $station)
    {
        $this->stations->assertDriverOwnsStation($station, Auth::id());
        $station->load(['connectors', 'facilities']);

        $pendingEdit = DriverStationAuditLog::where('station_id', $station->id)
            ->where('driver_id', Auth::id())
            ->where('action', 'edit')
            ->pending()
            ->latest()
            ->first();

        return view('driver.stations.edit', [
            'station'       => $station,
            'facilities'    => Facility::all(),
            'connectorRows' => $this->stations->resolveConnectorRowsForEdit($station, $pendingEdit),
        ]);
    }

    public function update(UpdateDriverStationRequest $request, Station $station)
    {
        $this->stations->assertDriverOwnsStation($station, Auth::id());

        if ($this->stations->hasPendingAudit($station)) {
            return back()->with('error', 'มีคำขอแก้ไข/ลบรออนุมัติอยู่แล้ว กรุณารอ Admin ตรวจสอบก่อนครับ');
        }

        $this->stations->submitEditRequest($station, Auth::user(), $request->validated(), $request);

        return redirect()->route('driver.account')
            ->with('success', 'ส่งคำขอแก้ไขสถานีแล้ว รอ Admin อนุมัติครับ');
    }

    public function destroy(DeleteDriverStationRequest $request, Station $station)
    {
        $this->stations->assertDriverOwnsStation($station, Auth::id());

        if ($this->stations->hasPendingAudit($station)) {
            return back()->with('error', 'มีคำขอแก้ไข/ลบรออนุมัติอยู่แล้ว กรุณารอ Admin ตรวจสอบก่อนครับ');
        }

        $this->stations->submitDeleteRequest($station, Auth::user(), $request->validated('reason'));

        return redirect()->route('driver.account')
            ->with('success', 'ส่งคำขอลบสถานีแล้ว รอ Admin อนุมัติครับ');
    }
}
