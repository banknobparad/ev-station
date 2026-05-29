<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\DriverStationAuditLog;
use App\Models\Facility;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DriverStationController extends Controller
{
    public function edit(Station $station)
    {
        $this->authorizeStation($station);

        $station->load(['connectors', 'facilities']);
        $facilities = Facility::all();

        return view('driver.stations.edit', [
            'station' => $station,
            'facilities' => $facilities,
        ]);
    }

    public function update(Request $request, Station $station)
    {
        $this->authorizeStation($station);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'lat'            => 'required|numeric',
            'lng'            => 'required|numeric',
            'open_time'      => 'nullable',
            'close_time'     => 'nullable',
            'image'          => 'nullable|image|max:10240',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|max:10240',

            'facilities'     => 'nullable|array',
            'facilities.*'   => 'integer|exists:facilities,id',

            'connectors'                 => 'nullable|array',
            'connectors.*.type'          => 'required_with:connectors.*.total|in:CCS2,CHAdeMO,Type2,GB/T',
            'connectors.*.total'         => 'required_with:connectors.*.type|integer|min:1',
        ]);

        $imagePath = $station->image;
        if ($request->hasFile('image')) {
            if (!empty($station->image)) {
                Storage::disk('public')->delete($station->image);
            }
            $imagePath = $this->compressAndStoreImage($request->file('image'), 'stations');
        }

        $galleryImages = collect($station->gallery_images ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        // ถ้าไม่ส่ง gallery_images มาเลย ให้คงของเดิมไว้
        if (!$request->hasFile('gallery_images')) {
            $galleryImages = collect($station->gallery_images ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();
        }


        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $galleryImage) {
                if (!$galleryImage) continue;
                $galleryImages[] = $this->compressAndStoreImage($galleryImage, 'stations');
            }
        }

        $station->update([
            'name'           => $validated['name'],
            'address'        => $validated['address'],
            'lat'            => $validated['lat'],
            'lng'            => $validated['lng'],
            'open_time'      => $validated['open_time'] ?? null,
            'close_time'     => $validated['close_time'] ?? null,
            'image'          => $imagePath,
            'gallery_images'=> $galleryImages,
        ]);

        if (array_key_exists('facilities', $validated)) {
            $station->facilities()->sync($validated['facilities'] ?? []);
        }

        // Replace connectors
        Connector::where('station_id', $station->id)->delete();
        if ($request->has('connectors') && is_array($request->connectors)) {
            foreach ($request->connectors as $connectorInput) {
                $type = $connectorInput['type'] ?? null;
                $total = $connectorInput['total'] ?? null;
                if (!$type || !$total) continue;

                $station->connectors()->create([
                    'type' => $type,
                    'total' => $total,
                ]);
            }
        }

        DriverStationAuditLog::create([
            'driver_id' => Auth::id(),
            'station_id' => $station->id,
            'action' => 'edit',
            'reason' => null,
            'payload' => [
                'name' => $validated['name'],
                'address' => $validated['address'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'open_time' => $validated['open_time'] ?? null,
                'close_time' => $validated['close_time'] ?? null,
                'facilities' => $validated['facilities'] ?? [],
                'connectors' => $validated['connectors'] ?? [],
                'image_updated' => $request->hasFile('image'),
                'gallery_added_count' => $request->hasFile('gallery_images') ? count($request->file('gallery_images')) : 0,
            ],
        ]);


        return redirect()->route('driver.account')->with('success', 'แก้ไขสถานีสำเร็จแล้วครับ (ส่งข้อมูลให้ Admin ตรวจสอบแล้ว)');
    }

    public function destroy(Request $request, Station $station)
    {
        $this->authorizeStation($station);

        $validated = $request->validate(
            ['reason' => 'required|string|max:1000'],
            ['reason.required' => 'กรุณากรอกเหตุผลที่ต้องการลบ']
        );


        // log before delete
        DriverStationAuditLog::create([
            'driver_id' => Auth::id(),
            'station_id' => $station->id,
            'action' => 'delete',
            'reason' => $validated['reason'],
            'payload' => null,
        ]);


        // delete media (best-effort)
        if (!empty($station->image)) {
            Storage::disk('public')->delete($station->image);
        }
        foreach (collect($station->gallery_images ?? []) as $img) {
            if ($img) Storage::disk('public')->delete($img);
        }

        $station->delete();

        return redirect()->route('driver.account')->with('success', 'ลบสถานีเรียบร้อยแล้วครับ');
    }

    private function authorizeStation(Station $station): void
    {
        if ($station->user_id !== Auth::id()) abort(403);
    }
}

