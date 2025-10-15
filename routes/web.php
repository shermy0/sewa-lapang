<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\Penyewa\FavoritController as PenyewaFavoritController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});


Route::middleware('auth')->group(function () {
    
    Route::get('/pemesanan/create/{lapangan}', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan/store', [PemesananController::class, 'store'])->name('pemesanan.store');
    Route::post('/pemesanan/update-status', [PemesananController::class, 'updateStatus'])->name('pemesanan.updateStatus');
    Route::get('/penyewa/riwayat', [PemesananController::class, 'riwayat'])->name('penyewa.riwayat');
    Route::post('/pemesanan/success/{id}', [PemesananController::class, 'updateSuccess']);
   
    // BERANDA PENYEWA
    Route::get('/beranda-penyewa', [BerandaController::class, 'index'])->name('penyewa.beranda');
    Route::get('/penyewa/detail/{id}', [BerandaController::class, 'detail'])->name('penyewa.detail');
   
    // ULASAN PENYEWA
    Route::post('/simpan/{lapangan}', [UlasanController::class, 'simpan'])->name('ulasan.simpan');
    Route::get('/{id}/edit', [UlasanController::class, 'edit'])->name('ulasan.edit');
    Route::put('/{id}/update', [UlasanController::class, 'update'])->name('ulasan.update');
    Route::delete('/{id}', [UlasanController::class, 'destroy'])->name('ulasan.hapus');

    // FAVORIT PENYEWA
    Route::get('favorit', [PenyewaFavoritController::class, 'index'])->name('favorit.index');
    Route::post('lapangan/{lapangan}/favorit', [PenyewaFavoritController::class, 'store'])->name('favorit.store');
    Route::delete('lapangan/{lapangan}/favorit', [PenyewaFavoritController::class, 'destroy'])->name('favorit.destroy');

    Route::post('/midtrans/token', [PemesananController::class, 'getSnapToken'])->name('midtrans.token');
    Route::get('/midtrans/token-again/{pemesanan}', [PemesananController::class, 'getSnapTokenAgain']);

    
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
});

Route::middleware('auth')->get('/test-sidebar', function () {
    return view('dashboard');
})->name('test.sidebar');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');