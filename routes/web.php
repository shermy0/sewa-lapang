<?php

use App\Http\Controllers\LapanganController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemilikDashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\LapanganController as AdminLapanganController;
use App\Http\Controllers\Admin\PemesananController as AdminPemesananController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;

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

Route::middleware(['auth', 'role:pemilik'])->group(function () {
    Route::get('/dashboard/pemilik', [PemilikDashboardController::class, 'index'])
        ->name('dashboard.pemilik');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->name('dashboard.admin');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.update-status');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('lapangan', [AdminLapanganController::class, 'index'])->name('lapangan.index');
        Route::put('lapangan/{lapangan}', [AdminLapanganController::class, 'update'])->name('lapangan.update');

        Route::get('pemesanan', [AdminPemesananController::class, 'index'])->name('pemesanan.index');
        Route::put('pemesanan/{pemesanan}', [AdminPemesananController::class, 'update'])->name('pemesanan.update');

        Route::get('pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
        Route::put('pembayaran/{pembayaran}', [AdminPembayaranController::class, 'update'])->name('pembayaran.update');
    });
});


Route::resource('lapangan', LapanganController::class);
