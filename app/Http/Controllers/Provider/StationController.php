<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Station\StoreProviderStationRequest;
use App\Http\Requests\Station\UpdateProviderStationRequest;
use App\Models\Facility;
use App\Models\Station;
use App\Services\StationService;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function __construct(private readonly StationService $stations)
    {
    }

    public function index()
    {
        $stations = Station::where('user_id', Auth::id())->get();

        return view('provider.stations.index', compact('stations'));
    }

    public function create()
    {
        return view('provider.stations.create', [
            'facilities' => Facility::all(),
        ]);
    }

    public function store(StoreProviderStationRequest $request)
    {
        $this->stations->createForProvider(Auth::user(), $request->validated(), $request);

        return redirect()->route('provider.stations.index')
            ->with('success', 'เพิ่มสถานีสำเร็จแล้วครับ');
    }

    public function edit(Station $station)
    {
        $this->stations->assertUserOwnsStation($station, Auth::id());

        return view('provider.stations.edit', [
            'station'    => $station,
            'facilities' => Facility::all(),
        ]);
    }

    public function update(UpdateProviderStationRequest $request, Station $station)
    {
        $this->stations->assertUserOwnsStation($station, Auth::id());
        $this->stations->updateDirect($station, $request->validated(), $request);

        return redirect()->route('provider.stations.index')
            ->with('success', 'แก้ไขสถานีสำเร็จแล้วครับ');
    }

    public function destroy(Station $station)
    {
        $this->stations->assertUserOwnsStation($station, Auth::id());
        $this->stations->deleteStation($station);

        return redirect()->route('provider.stations.index')
            ->with('success', 'ลบสถานีสำเร็จแล้วครับ');
    }
}
