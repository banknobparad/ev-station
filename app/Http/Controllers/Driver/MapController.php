<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Station;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $stations = Station::with('connectors')
            ->where('approval_status', 'approved')
            ->get();

        return view('driver.map', compact('stations'));
    }

    public function show(Request $request, Station $station)
    {
        $station->load('connectors');

        $showAllReviews = $request->query('view') === 'all';
        $reviewsQuery = $station->reviews()->latest();

        $stationReviews = $showAllReviews
            ? $reviewsQuery->with('user')->get()
            : $reviewsQuery->with('user')->limit(5)->get();

        $station->setRelation('reviews', $stationReviews);

        $reviewImages = Review::where('station_id', $station->id)
            ->whereNotNull('images')
            ->pluck('images')
            ->flatten()
            ->filter()
            ->values()
            ->all();

        $myReview = null;
        if (auth()->check()) {
            $myReview = Review::where('station_id', $station->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('driver.station', compact('station', 'reviewImages', 'showAllReviews', 'myReview'));
    }
}
