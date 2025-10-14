<?php

use App\Http\Controllers\LapanganController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemilikDashboardController;
use App\Http\Controllers\PemilikPemesananController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});



// Route::middleware('auth')->get('/test-sidebar', function () {
//     return view('dashboard');
// })->name('test.sidebar');


Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/pemilik', [PemilikDashboardController::class, 'index'])
        ->name('dashboard.pemilik');

    Route::get('/dashboard/pemilik/pemesanan', [PemilikPemesananController::class, 'index'])
        ->name('pemilik.pemesanan.index');

    Route::patch('/dashboard/pemilik/pemesanan/{pemesanan}', [PemilikPemesananController::class, 'updateStatus'])
        ->name('pemilik.pemesanan.update');
});


Route::resource('lapangan', LapanganController::class);

