<?php

use App\Http\Controllers\LapanganController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemilikDashboardController;
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
});

Route::middleware(['auth'])->group(function () {
    // CRUD Lapangan
    Route::get('/lapangan', [LapanganController::class, 'index'])->name('lapangan.index');
    Route::post('/lapangan', [LapanganController::class, 'store'])->name('lapangan.store');
    Route::get('/lapangan/{id}', [LapanganController::class, 'show'])->name('lapangan.show');
    Route::put('/lapangan/{id}', [LapanganController::class, 'update'])->name('lapangan.update');
    Route::delete('/lapangan/{id}', [LapanganController::class, 'destroy'])->name('lapangan.destroy');
    
    // Jadwal Lapangan
    Route::post('/lapangan/{lapanganId}/jadwal', [LapanganController::class, 'storeJadwal'])->name('lapangan.jadwal.store');
    Route::delete('/lapangan/{lapanganId}/jadwal/{jadwalId}', [LapanganController::class, 'destroyJadwal'])->name('lapangan.jadwal.destroy');
    
    // API Tiket (optional)
    Route::post('/lapangan/{id}/reduce-ticket/{quantity?}', [LapanganController::class, 'reduceTicket'])->name('lapangan.reduceTicket');
    Route::post('/lapangan/{id}/add-ticket/{quantity?}', [LapanganController::class, 'addTicket'])->name('lapangan.addTicket');
});


