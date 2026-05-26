<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // toggle favorite — กดซ้ำเพื่อเอาออก
    public function toggle(Station $station)
    {
        $existing = Favorite::where('user_id', Auth::id())
                            ->where('station_id', $station->id)
                            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'เอาออกจาก Favorite แล้วครับ');
        }

        Favorite::create([
            'user_id'    => Auth::id(),
            'station_id' => $station->id,
        ]);

        return back()->with('success', 'เพิ่มใน Favorite แล้วครับ ❤️');
    }

    // หน้ารายการ Favorite
    public function index()
    {
        $favorites = Favorite::where('user_id', Auth::id())
                             ->with('station.connectors')
                             ->get();

        return view('driver.favorites', compact('favorites'));
    }
}