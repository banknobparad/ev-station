<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Station\ConnectorRequest;
use App\Http\Requests\Station\UpdateAdminStationRequest;
use App\Models\Connector;
use App\Models\DriverStationAuditLog;
use App\Models\Review;
use App\Models\Station;
use App\Services\StationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StationManagementController extends Controller
{
    public function __construct(private readonly StationService $stations)
    {
    }

    public function index()
    {
        $stations = Station::with(['user', 'connectors', 'facilities'])
            ->latest()
            ->get();

        return view('admin.stations.index', compact('stations'));
    }

    public function pending()
    {
        $pendingStations = Station::with('user')
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        return view('admin.stations.pending', compact('pendingStations'));
    }

    public function approve(Station $station)
    {
        $station->update(['approval_status' => 'approved']);

        return redirect()->route('admin.dashboard')
            ->with('success', 'อนุมัติสถานีสำเร็จแล้วครับ');
    }

    public function reject(Station $station)
    {
        $this->stations->deleteStation($station);

        return redirect()->route('admin.dashboard')
            ->with('success', 'ปฏิเสธสถานีแล้วครับ');
    }

    public function approveDriverLog(DriverStationAuditLog $log)
    {
        if (!$log->isPending()) {
            return back()->with('error', 'คำขอนี้ถูกดำเนินการไปแล้ว');
        }

        $action = $log->action;
        $this->stations->approveAuditLog($log);

        $message = $action === 'delete'
            ? 'อนุมัติการลบสถานีเรียบร้อยแล้ว'
            : 'อนุมัติการแก้ไขสถานีเรียบร้อยแล้ว';

        return back()->with('success', $message);
    }

    public function rejectDriverLog(DriverStationAuditLog $log)
    {
        if (!$log->isPending()) {
            return back()->with('error', 'คำขอนี้ถูกดำเนินการไปแล้ว');
        }

        $this->stations->rejectAuditLog($log);

        return back()->with('success', 'ยกเลิกคำขอเรียบร้อยแล้ว');
    }

    public function show(Station $station)
    {
        $station->load(['user', 'connectors', 'facilities', 'reviews.user']);

        return view('admin.stations.show', compact('station'));
    }

    public function update(UpdateAdminStationRequest $request, Station $station)
    {
        $this->stations->updateDirect($station, $request->validated(), $request);

        return redirect()
            ->route('admin.stations.show', $station)
            ->with('success', 'แก้ไขสถานีสำเร็จแล้วครับ');
    }

    public function destroy(Station $station)
    {
        $this->stations->deleteStation($station);

        return redirect()->route('admin.stations.index')
            ->with('success', 'ลบสถานีสำเร็จแล้วครับ');
    }

    public function deleteStationImage(Station $station)
    {
        if (!empty($station->image)) {
            Storage::disk('public')->delete($station->image);
        }

        $station->update(['image' => null]);

        return back()->with('success', 'ลบรูปภาพหลักแล้วครับ');
    }

    public function deleteGalleryImage(Request $request, Station $station)
    {
        $data = $request->validate([
            'image_path' => 'required|string',
        ]);

        $gallery = collect($station->gallery_images ?? [])
            ->filter()
            ->unique();

        if ($gallery->contains($data['image_path'])) {
            Storage::disk('public')->delete($data['image_path']);
        }

        $station->update([
            'gallery_images' => $gallery
                ->reject(fn ($img) => $img === $data['image_path'])
                ->values()
                ->all(),
        ]);

        return back()->with('success', 'ลบรูปภาพในแกลลอรีแล้วครับ');
    }

    public function updateConnector(ConnectorRequest $request, Station $station, Connector $connector)
    {
        if ($connector->station_id !== $station->id) {
            abort(404);
        }

        $validated = $request->validated();

        if ($connector->type !== $validated['type']) {
            $exists = $station->connectors()
                ->where('type', $validated['type'])
                ->where('id', '!=', $connector->id)
                ->exists();

            if ($exists) {
                return back()->with('error', 'ประเภทหัวชาร์จนี้มีอยู่แล้วในสถานีนี้');
            }
        }

        $connector->update($validated);

        return back()->with('success', 'แก้ไขหัวชาร์จแล้วครับ');
    }

    public function destroyConnector(Station $station, Connector $connector)
    {
        if ($connector->station_id !== $station->id) {
            abort(404);
        }

        $connector->delete();

        return back()->with('success', 'ลบหัวชาร์จแล้วครับ');
    }

    public function addConnector(ConnectorRequest $request, Station $station)
    {
        $validated = $request->validated();

        $station->connectors()->updateOrCreate(
            ['type' => $validated['type']],
            ['total' => $validated['total']]
        );

        return back()->with('success', 'เพิ่มหัวชาร์จสำเร็จแล้วครับ');
    }

    public function destroyReview(Review $review)
    {
        $stationId = $review->station_id;
        $review->delete();

        return redirect()->route('admin.stations.show', $stationId)
            ->with('success', 'ลบคอมเมนต์สำเร็จแล้วครับ');
    }
}
