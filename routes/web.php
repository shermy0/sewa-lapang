<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\UlasanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Routes untuk guest (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Routes untuk user yang sudah login
Route::middleware('auth')->group(function () {

    // Verifikasi Email
    Route::get('/verify-email', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.success');
        }
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('verification.success');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.success');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'verification' => __('Email verifikasi gagal dikirim. Silakan coba lagi nanti.'),
            ]);
        }

        return back()->with('status', __('Email verifikasi baru telah dikirim.'));
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('/verifikasi-berhasil', function () {
        return redirect('/')->with('status', __('Akun berhasil diverifikasi.'));
    })->name('verification.success');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard / Test Sidebar
    Route::get('/test-sidebar', function () {
        return view('dashboard');
    })->name('test.sidebar');

    /*
    |--------------------------------------------------------------------------
    | Routes Penyewa
    |--------------------------------------------------------------------------
    */
    Route::prefix('penyewa')->name('penyewa.')->group(function () {
        Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
        Route::get('/pemesanan', [BerandaController::class, 'pemesanan'])->name('pemesanan');
        Route::get('/pembayaran', [BerandaController::class, 'pembayaran'])->name('pembayaran');
        Route::get('/riwayat', [BerandaController::class, 'riwayat'])->name('riwayat');
        Route::get('/akun', [BerandaController::class, 'akun'])->name('akun');
        Route::get('/detail/{id}', [BerandaController::class, 'detail'])->name('detail');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Ulasan Penyewa
    |--------------------------------------------------------------------------
    */
    Route::prefix('ulasan')->group(function () {
        Route::post('/simpan/{lapangan}', [UlasanController::class, 'simpan'])->name('ulasan.simpan');
        Route::get('/{id}/edit', [UlasanController::class, 'edit'])->name('ulasan.edit');
        Route::put('/{id}/update', [UlasanController::class, 'update'])->name('ulasan.update');
        Route::delete('/{id}', [UlasanController::class, 'destroy'])->name('ulasan.hapus');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Pemilik (opsional, jika ada role pemilik)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pemilik')->name('pemilik.')->group(function () {
        Route::get('/dashboard', [BerandaController::class, 'dashboardPemilik'])->name('dashboard');
        Route::get('/lapangan', [BerandaController::class, 'lapangan'])->name('lapangan');
        Route::get('/pemesanan', [BerandaController::class, 'pemesananPemilik'])->name('pemesanan');
        Route::get('/pembayaran', [BerandaController::class, 'pembayaranPemilik'])->name('pembayaran');
        Route::get('/laporan', [BerandaController::class, 'laporan'])->name('laporan');
        Route::get('/pengguna', [BerandaController::class, 'pengguna'])->name('pengguna');
        Route::get('/akun', [BerandaController::class, 'akunPemilik'])->name('akun');
    });
});
