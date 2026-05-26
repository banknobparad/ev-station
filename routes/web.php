<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

// 1. เพิ่มและแก้ไขตรงนี้: ดักจับทั้งหน้าแรก (/) และ (/home) ให้เช็ค Role แล้ว Redirect
Route::match(['get', 'post'], '/', function () {
    $user = Auth::user();

    // เช็ค Role ของผู้ใช้ (ใช้สิทธิ์อ้างอิงตาม RoleMiddleware ของคุณ)
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'provider') {
        return redirect()->route('provider.dashboard');
    } elseif ($user->role === 'driver') {
        return redirect()->route('driver.map');
    }

    return abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ หรือไม่มี Role ในระบบ');
})->middleware('auth');

// หากมีระบบอื่นวิ่งมาที่ /home ให้โยนกลับมาจัดระบบที่หน้าแรก (/)
Route::get('/home', function () {
    return redirect('/');
})->middleware('auth');


Auth::routes();

// ==========================================
// หน้า Driver
// ==========================================
Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/map', [App\Http\Controllers\Driver\MapController::class, 'index'])->name('driver.map');
    Route::get('/station/{station}', [App\Http\Controllers\Driver\MapController::class, 'show'])->name('driver.station');

    // Review
    Route::post('/station/{station}/review', [App\Http\Controllers\Driver\ReviewController::class, 'store'])->name('driver.review.store');
    Route::delete('/review/{review}', [App\Http\Controllers\Driver\ReviewController::class, 'destroy'])->name('driver.review.destroy');

    // Favorite
    Route::post('/station/{station}/favorite', [App\Http\Controllers\Driver\FavoriteController::class, 'toggle'])->name('driver.favorite.toggle');
    Route::get('/favorites', [App\Http\Controllers\Driver\FavoriteController::class, 'index'])->name('driver.favorites');
});

// ==========================================
// หน้า Provider
// ==========================================
Route::middleware(['auth', 'role:provider'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('/', [App\Http\Controllers\Provider\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('stations', App\Http\Controllers\Provider\StationController::class);

    Route::resource('stations.connectors', App\Http\Controllers\Provider\ConnectorController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy']);
});

// ==========================================
// หน้า Admin
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)
        ->only(['index', 'create', 'store', 'destroy']);

    Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{station}', [App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
});
