<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== หน้าหลัก (Landing Page) =====
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ===== Auth Routes =====
// Register
Route::get('/register',          [App\Http\Controllers\Auth\RegisterController::class, 'registerStep1'])->name('register.phone');
Route::post('/register',         [App\Http\Controllers\Auth\RegisterController::class, 'registerStep1Post']);

// Login
Route::get('/login',             [App\Http\Controllers\Auth\RegisterController::class, 'loginStep1'])->name('login');
Route::post('/login',            [App\Http\Controllers\Auth\RegisterController::class, 'loginStep1Post'])->name('login.phone');
Route::get('/login/reset',       [App\Http\Controllers\Auth\RegisterController::class, 'resetLogin'])->name('login.reset');
Route::get('/login/otp',         [App\Http\Controllers\Auth\RegisterController::class, 'loginOtp'])->name('login.otp');
Route::post('/login/otp',        [App\Http\Controllers\Auth\RegisterController::class, 'loginOtpPost']);
Route::get('/login/password',    [App\Http\Controllers\Auth\RegisterController::class, 'loginPassword'])->name('login.password');
Route::post('/login/password',   [App\Http\Controllers\Auth\RegisterController::class, 'loginPasswordPost']);

// Logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

Route::get('/home', function () { return redirect('/'); })->middleware('auth');

// ===== Driver =====
Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/map', [App\Http\Controllers\Driver\MapController::class, 'index'])->name('driver.map');
    Route::get('/account', [App\Http\Controllers\Driver\AccountController::class, 'index'])->name('driver.account');
    Route::post('/account', [App\Http\Controllers\Driver\AccountController::class, 'update'])->name('driver.account.update');

    // Driver add station (pending)
    Route::get('/account/stations/create', [App\Http\Controllers\Driver\StationController::class, 'create'])->name('driver.stations.create');
    Route::post('/account/stations', [App\Http\Controllers\Driver\StationController::class, 'store'])->name('driver.stations.store');

    // Driver edit/delete own stations (+ audit logs)
// เปลี่ยนจาก DriverStationController::class เป็น StationController::class
Route::get('/account/stations/{station}/edit',   [App\Http\Controllers\Driver\StationController::class, 'edit'])->name('driver.stations.edit');
Route::put('/account/stations/{station}',        [App\Http\Controllers\Driver\StationController::class, 'update'])->name('driver.stations.update');
Route::delete('/account/stations/{station}',     [App\Http\Controllers\Driver\StationController::class, 'destroy'])->name('driver.stations.destroy');


    Route::get('/station/{station}', [App\Http\Controllers\Driver\MapController::class, 'show'])->name('driver.station');
    Route::post('/station/{station}/review', [App\Http\Controllers\Driver\ReviewController::class, 'store'])->name('driver.review.store');
    Route::delete('/review/{review}', [App\Http\Controllers\Driver\ReviewController::class, 'destroy'])->name('driver.review.destroy');
    Route::post('/station/{station}/favorite', [App\Http\Controllers\Driver\FavoriteController::class, 'toggle'])->name('driver.favorite.toggle');
    Route::get('/favorites', [App\Http\Controllers\Driver\FavoriteController::class, 'index'])->name('driver.favorites');
    Route::get('/review/{review}/edit', [App\Http\Controllers\Driver\ReviewController::class, 'edit'])->name('driver.review.edit');
    Route::put('/review/{review}', [App\Http\Controllers\Driver\ReviewController::class, 'update'])->name('driver.review.update');
});

// ===== Provider =====
Route::middleware(['auth', 'role:provider'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('/', [App\Http\Controllers\Provider\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('stations', App\Http\Controllers\Provider\StationController::class);
    Route::resource('stations.connectors', App\Http\Controllers\Provider\ConnectorController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy']);
});

// ===== Admin =====
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Stations approval
    Route::get('stations/pending', [App\Http\Controllers\Admin\StationApprovalController::class, 'indexPending'])->name('stations.pending');
    Route::post('stations/{station}/approve', [App\Http\Controllers\Admin\StationApprovalController::class, 'approve'])->name('stations.approve');
    Route::delete('stations/{station}/reject', [App\Http\Controllers\Admin\StationApprovalController::class, 'reject'])->name('stations.reject');

    // Stations management
    Route::get('stations', [App\Http\Controllers\Admin\StationManagementController::class, 'index'])->name('stations.index');
    Route::get('stations/{station}', [App\Http\Controllers\Admin\StationManagementController::class, 'show'])->name('stations.show');
    Route::put('stations/{station}', [App\Http\Controllers\Admin\StationManagementController::class, 'update'])->name('stations.update');
    Route::delete('stations/{station}', [App\Http\Controllers\Admin\StationManagementController::class, 'destroy'])->name('stations.destroy');

    Route::post('stations/{station}/image/delete', [App\Http\Controllers\Admin\StationManagementController::class, 'deleteStationImage'])
        ->name('stations.delete_station_image');
    Route::post('stations/{station}/gallery-image/delete', [App\Http\Controllers\Admin\StationManagementController::class, 'deleteGalleryImage'])
        ->name('stations.delete_gallery_image');

    Route::post('stations/{station}/connectors', [App\Http\Controllers\Admin\StationManagementController::class, 'addConnector'])
        ->name('stations.connectors.add');
    Route::put('stations/{station}/connectors/{connector}', [App\Http\Controllers\Admin\StationManagementController::class, 'updateConnector'])
        ->name('stations.connectors.update');
    Route::delete('stations/{station}/connectors/{connector}', [App\Http\Controllers\Admin\StationManagementController::class, 'destroyConnector'])
        ->name('stations.connectors.destroy');

    Route::delete('stations/reviews/{review}', [App\Http\Controllers\Admin\StationManagementController::class, 'destroyReview'])
        ->name('stations.reviews.destroy');

    Route::resource('users', App\Http\Controllers\Admin\UserController::class)
        ->only(['index', 'create', 'store', 'destroy']);

    Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{station}', [App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
});

