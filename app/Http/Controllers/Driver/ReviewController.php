<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Station $station)
    {
        $request->validate([
            'star'    => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // กันรีวิวซ้ำ
        $existing = Review::where('user_id', Auth::id())
                          ->where('station_id', $station->id)
                          ->first();

        if ($existing) {
            return back()->with('error', 'คุณรีวิวสถานีนี้ไปแล้วครับ');
        }

        Review::create([
            'user_id'    => Auth::id(),
            'station_id' => $station->id,
            'star'       => $request->star,
            'comment'    => $request->comment,
        ]);

        return back()->with('success', 'รีวิวสำเร็จแล้วครับ ขอบคุณ!');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) abort(403);
        $review->delete();
        return back()->with('success', 'ลบรีวิวสำเร็จแล้วครับ');
    }
}