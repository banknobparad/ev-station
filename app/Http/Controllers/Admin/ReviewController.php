<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Station;

class ReviewController extends Controller
{
    // แสดงสถานีทั้งหมด
    public function index()
    {
        $stations = Station::withCount('reviews')->get();
        return view('admin.reviews.index', compact('stations'));
    }

    // แสดง Comment ของสถานีนั้น
    public function show(Station $station)
    {
        $reviews = $station->reviews()->with('user')->latest()->get();
        return view('admin.reviews.show', compact('station', 'reviews'));
    }

    // ลบ Comment
    public function destroy(Review $review)
    {
        $stationId = $review->station_id;
        $review->delete();
        return redirect()->route('admin.reviews.show', $stationId)
                         ->with('success', 'ลบคอมเมนต์สำเร็จแล้วครับ');
    }
}