<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\LapanganController as AdminLapanganController;
use App\Http\Controllers\Admin\LaporanPenyalahgunaanController as AdminLaporanPenyalahgunaanController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DisbursementController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\Penyewa\FavoritController as PenyewaFavoritController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KelolaRekeningController;
use App\Http\Controllers\PemilikDashboardController;
use App\Http\Controllers\ScanTiketController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\KategoriController;


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
    // Route::get('/penyewa/riwayat', [PemesananController::class, 'riwayat'])->name('penyewa.riwayat');
    Route::post('/pemesanan/success/{id}', [PemesananController::class, 'updateSuccess']);

    Route::post('/midtrans/callback', [PemesananController::class, 'updateSuccess']);
    Route::post('/midtrans/token', [PemesananController::class, 'getSnapToken'])->name('midtrans.token');
    Route::get('/midtrans/token-again/{pemesanan}', [PemesananController::class, 'getSnapTokenAgain']);

    Route::delete('/pemesanan/batalkan/{id}', [PemesananController::class, 'batalkan'])->name('pemesanan.batalkan');
Route::get('/tiket/download/{id}', [PemesananController::class, 'downloadTiket'])->name('tiket.download');

    Route::get('penyewa/tiket', [PemesananController::class, 'riwayatTiket'])->name('penyewa.tiket');
Route::get('penyewa/pembayaran', [PemesananController::class, 'riwayatBelum'])->name('penyewa.pembayaran');
Route::get('penyewa/riwayat', [PemesananController::class, 'riwayatBatal'])->name('penyewa.riwayat');

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

    Route::get('/verifikasi-berhasil', function (Request $request) {
        $user = $request->user();

        if (! $user) {
            return redirect('/')->with('status', __('Akun berhasil diverifikasi.'));
        }

        if ($user->role === 'penyewa') {
            return redirect()->route('penyewa.beranda')->with('status', __('Akun berhasil diverifikasi.'));
        }

        if ($user->role === 'pemilik') {
            return redirect()->route('dashboard.pemilik')->with('status', __('Akun berhasil diverifikasi.'));
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard.admin');
        }

        return redirect('/')->with('status', __('Akun berhasil diverifikasi.'));
    })->name('verification.success');
});

Route::middleware('auth')->get('/test-sidebar', function () {
    return view('dashboard');
})->name('test.sidebar');
Route::middleware(['auth'])->group(function () {
    Route::get('/kelola-rekening', [KelolaRekeningController::class, 'index'])->name('rekening.index');
    Route::post('/kelola-rekening', [KelolaRekeningController::class, 'update'])->name('rekening.update');
     Route::post('/rekening/cairkan', [DisbursementController::class, 'kirimDana'])->name('rekening.cairkan');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/hapus-foto', [ProfileController::class, 'hapusFoto'])->name('profile.hapusFoto');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

});

// Route::middleware('auth')->get('/test-sidebar', function () {
//     return view('dashboard');
// })->name('test.sidebar');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/pemilik', [PemilikDashboardController::class, 'index'])->name('dashboard.pemilik');
    Route::get('/favorit/pemilik', [FavoritController::class, 'index'])->name('pemilik.favorit');
    Route::get('/pemilik/scan', [ScanTiketController::class, 'index'])->name('pemilik.scan');
    Route::get('/verify-tiket/{kode}', [ScanTiketController::class, 'verifyTiket']);
});


Route::middleware(['auth'])->group(function () {
    // CRUD Kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show');
    Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // CRUD Lapangan
    Route::get('/lapangan', [LapanganController::class, 'index'])->name('lapangan.index');
    Route::post('/lapangan', [LapanganController::class, 'store'])->name('lapangan.store');
    Route::get('/lapangan/{id}', [LapanganController::class, 'show'])->name('lapangan.show');
    Route::put('/lapangan/{id}', [LapanganController::class, 'update'])->name('lapangan.update');
    Route::delete('/lapangan/{id}', [LapanganController::class, 'destroy'])->name('lapangan.destroy');

    // Jadwal Lapangan
    Route::post('/lapangan/{lapanganId}/jadwal', [LapanganController::class, 'storeJadwal'])->name('lapangan.jadwal.store');
    Route::put('/lapangan/{lapanganId}/jadwal/{jadwalId}', [LapanganController::class, 'updateJadwal'])->name('lapangan.jadwal.update');
    Route::delete('/lapangan/{lapanganId}/jadwal/{jadwalId?}', [LapanganController::class, 'destroyJadwal'])->name('lapangan.jadwal.destroy');

    // API Tiket (optional)
    Route::post('/lapangan/{id}/reduce-ticket/{quantity?}', [LapanganController::class, 'reduceTicket'])->name('lapangan.reduceTicket');
    Route::post('/lapangan/{id}/add-ticket/{quantity?}', [LapanganController::class, 'addTicket'])->name('lapangan.addTicket');
});
