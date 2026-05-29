<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\Review;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StationManagementController extends Controller
{
    public function index()
    {
        $stations = Station::with(['user', 'connectors', 'facilities'])
            ->latest()
            ->get();

        return view('admin.stations.index', compact('stations'));
    }

    public function show(Station $station)
    {
        $station->load(['user', 'connectors', 'facilities', 'reviews.user']);

        return view('admin.stations.show', compact('station'));
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'required|string',
            'lat'        => 'required|numeric',
            'lng'        => 'required|numeric',
            'open_time'  => 'nullable',
            'close_time' => 'nullable',
            'image'      => 'nullable|image|max:10240',
            'gallery_images'   => 'nullable|array',
            'gallery_images.*' => 'nullable|image|max:10240',
            'facilities'       => 'nullable|array',
            'facilities.*'     => 'integer|exists:facilities,id',
        ]);

        $imagePath = $station->image;
        if ($request->hasFile('image')) {
            if (!empty($station->image)) {
                Storage::disk('public')->delete($station->image);
            }
            $imagePath = $this->compressAndStoreImage($request->file('image'), 'stations');



        }

        $gallery = collect($station->gallery_images ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $galleryImage) {
                if (!$galleryImage) continue;
                $gallery[] = $this->compressAndStoreImage($galleryImage, 'stations');
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
            'gallery_images'=> $gallery,
        ]);

        if (array_key_exists('facilities', $validated)) {
            $station->facilities()->sync($validated['facilities'] ?? []);
        }

        return redirect()
            ->route('admin.stations.show', $station)
            ->with('success', 'แก้ไขสถานีสำเร็จแล้วครับ');
    }

    public function destroy(Station $station)
    {
        // ลบรูปจาก storage (หากเก็บเป็น path)
        if (!empty($station->image)) {
            Storage::disk('public')->delete($station->image);
        }

        $gallery = collect($station->gallery_images ?? [])
            ->filter()
            ->unique();
        foreach ($gallery as $img) {
            Storage::disk('public')->delete($img);
        }

        $station->delete();

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

        $gallery = $gallery->reject(fn($img) => $img === $data['image_path'])->values()->all();

        $station->update(['gallery_images' => $gallery]);

        return back()->with('success', 'ลบรูปภาพในแกลลอรีแล้วครับ');
    }

    public function updateConnector(Request $request, Station $station, Connector $connector)
    {
        $validated = $request->validate([
            'type'  => 'required|in:CCS2,CHAdeMO,Type2,GB/T',
            'total' => 'required|integer|min:1',
        ]);

        // ป้องกันแก้ connector ของสถานีอื่น
        if ($connector->station_id !== $station->id) {
            abort(404);
        }

        $connector->update([
            'type'  => $validated['type'],
            'total' => $validated['total'],
        ]);

        return back()->with('success', 'แก้ไขหัวชาร์จแล้วครับ');
    }

    public function destroyConnector(Station $station, Connector $connector)
    {
        if ($connector->station_id !== $station->id) abort(404);

        $connector->delete();

        return back()->with('success', 'ลบหัวชาร์จแล้วครับ');
    }

    public function addConnector(Request $request, Station $station)
    {
        $validated = $request->validate([
            'type'  => 'required|in:CCS2,CHAdeMO,Type2,GB/T',
            'total' => 'required|integer|min:1',
        ]);

        Connector::create([
            'station_id' => $station->id,
            'type'       => $validated['type'],
            'total'      => $validated['total'],
        ]);

        return back()->with('success', 'เพิ่มหัวชาร์จสำเร็จแล้วครับ');
    }

    public function destroyReview(Review $review)
    {
        $stationId = $review->station_id;
        $review->delete();

        return redirect()->route('admin.stations.show', $stationId)
            ->with('success', 'ลบคอมเมนต์สำเร็จแล้วครับ');
    }

    // ใช้ compressAndStoreImage จาก Base Controller (App\Http\Controllers\Controller)

}

